<?php
namespace App\Controllers;

use App\Models\Listing;
use App\Models\Reservation;
use App\Models\PickupSlot;
use App\Models\Notification;
use App\Services\DiscountEngine;
use App\Services\PriorityWindowService;
use App\Services\ListingStateMachine;
use App\Services\NotificationService;

/**
 * ConsumerController — Member C's vertical slice.
 *
 * Use cases covered:
 *   UC-16  Browse unclaimed listings          →  browse()
 *   UC-17  View discount tiers                →  browse() + show()
 *   UC-18  Make pre-payment (simulated)       →  checkout() + confirmCheckout()
 *   UC-19  Schedule collection slot           →  scheduleSlot() (part of checkout flow)
 *   UC-14  Confirm successful pickup          →  confirmPickup() (shared with Charity)
 *   CRUD - Delete/cancel own reservation      →  cancelReservation()
 */
class ConsumerController extends BaseController
{
    /* --------------------------------------------------------- */
    /* GET /consumer/dashboard  →  landing (redirect to listings) */
    /* --------------------------------------------------------- */
    public function dashboard(): void
    {
        $this->requireRole('consumer');
        $this->redirect(BASE_URL . '/consumer/listings');
    }

    /* --------------------------------------------------------- */
    /* GET /consumer/listings   →  UC-16 & UC-17                 */
    /*   Browse unclaimed listings + view discount tiers.        */
    /*   Alternate flow: charity priority window still open →    */
    /*   show info banner and return early.                      */
    /* --------------------------------------------------------- */
    public function browse(): void
    {
        $this->requireRole('consumer');

        $category = $_GET['category'] ?? 'all';
        if (!in_array($category, ['all', 'fruit', 'vegetable'], true)) {
            $category = 'all';
        }

        // Location filter: set once the customer picks an address from
        // the Places Autocomplete field (see browse.php + location-filter.js,
        // which fill these as hidden lat/lng inputs on the filter form).
        $address = trim($_GET['address'] ?? '');
        $lat     = isset($_GET['lat']) && $_GET['lat'] !== '' ? (float) $_GET['lat'] : null;
        $lng     = isset($_GET['lng']) && $_GET['lng'] !== '' ? (float) $_GET['lng'] : null;
        $radiusKm = 30;

        $selectedOutletId = isset($_GET['outlet_id']) && $_GET['outlet_id'] !== ''
            ? (int) $_GET['outlet_id']
            : null;

        $listingModel = new Listing();
        $outletModel  = new \App\Models\Outlet();
        $priority     = new PriorityWindowService();
        $discount     = new DiscountEngine();

        // If the charity priority window is still open, consumers
        // must wait. We still render the page (empty state), so they
        // see WHY nothing is listed.
        $windowOpen        = $priority->isCharityWindowOpen();
        $consumerCanBrowse = $priority->isConsumerWindowOpen();

        $nearbyOutlets = [];
        $outletIds     = null; // null = no location filter applied yet

        if ($lat !== null && $lng !== null) {
            $nearbyOutlets = $outletModel->findNearby($lat, $lng, $radiusKm);
            $nearbyIds     = array_column($nearbyOutlets, 'id');

            // A specific branch was picked from the dropdown — only honour
            // it if that branch is actually within the radius, otherwise
            // fall back to "all nearby outlets" rather than silently
            // showing an out-of-range branch's stock.
            if ($selectedOutletId !== null && in_array($selectedOutletId, $nearbyIds, true)) {
                $outletIds = [$selectedOutletId];
            } else {
                $selectedOutletId = null;
                $outletIds        = $nearbyIds;
            }
        }

        $rawListings = $consumerCanBrowse
            ? $listingModel->findUnclaimedForConsumers($category, $outletIds)
            : [];

        // Decorate each listing with the computed discount tier so
        // the View is a pure renderer (no business logic).
        $listings = array_map(function (array $row) use ($discount) {
            $expiryDate         = new \DateTime($row['expiry_date']);
            $row['discount_pct']    = $discount->calculateDiscountPercent($expiryDate);
            $row['reference_price'] = (float)($row['reference_price'] ?? 0.0);
            $row['final_price']     = $discount->applyDiscount(
                $row['reference_price'], $row['discount_pct']
            );
            $row['image']           = $this->imageFor($row['item_name'], $row['category']);
            return $row;
        }, $rawListings);

        $this->render('consumer/browse', [
            'title'              => 'Marketplace',
            'active'             => 'marketplace',
            'crumb'              => 'Marketplace',
            'listings'           => $listings,
            'selectedCategory'   => $category,
            'consumerCanBrowse'  => $consumerCanBrowse,
            'charityWindowOpen'  => $windowOpen,
            'address'            => $address,
            'lat'                => $lat,
            'lng'                => $lng,
            'radiusKm'           => $radiusKm,
            'nearbyOutlets'      => $nearbyOutlets,
            'selectedOutletId'   => $selectedOutletId,
        ]);
    }

    /* --------------------------------------------------------- */
    /* GET /consumer/listings/{id}/checkout   →  UC-18 form      */
    /* --------------------------------------------------------- */
    public function checkout(int $id): void
    {
        $this->requireRole('consumer');

        $listingModel = new Listing();
        $slotModel    = new PickupSlot();
        $discount     = new DiscountEngine();

        $listing = $listingModel->find($id);
        if (!$listing || $listing['status'] !== 'available'
            || (float)$listing['quantity_remaining_kg'] <= 0) {
            $this->flash('error', 'That listing is no longer available.');
            $this->redirect(BASE_URL . '/consumer/listings');
        }

        $expiryDate = new \DateTime($listing['expiry_date']);
        $listing['discount_pct']    = $discount->calculateDiscountPercent($expiryDate);
        $listing['reference_price'] = (float)($listing['reference_price'] ?? 0.0);
        $listing['final_price']     = $discount->applyDiscount(
            $listing['reference_price'], $listing['discount_pct']
        );

        $slots = $slotModel->findAvailableForListing($id);

        $this->render('consumer/checkout', [
            'title'   => 'Checkout',
            'active'  => 'marketplace',
            'crumb'   => 'Checkout',
            'listing' => $listing,
            'slots'   => $slots,
            'errors'  => [],
            'input'   => $_SESSION['checkout_input'] ?? [],
        ]);
    }

    /* --------------------------------------------------------- */
    /* POST /consumer/listings/{id}/checkout →  UC-18 + UC-19    */
    /*   Reserves a quantity, locks a pickup slot, records the   */
    /*   discount tier and simulated pre-payment amount.         */
    /* --------------------------------------------------------- */
    public function confirmCheckout(int $id): void
    {
        $user = $this->requireRole('consumer');
        $this->verifyCsrf();

        $listingModel = new Listing();
        $slotModel    = new PickupSlot();
        $resModel     = new Reservation();
        $discount     = new DiscountEngine();
        $notifier     = new NotificationService();

        $listing = $listingModel->find($id);
        if (!$listing) {
            $this->flash('error', 'Listing not found.');
            $this->redirect(BASE_URL . '/consumer/listings');
        }

        // ---- validate ----
        $qty    = (float)($_POST['quantity_kg'] ?? 0);
        $slotId = (int)($_POST['pickup_slot_id'] ?? 0);
        $errors = [];

        if ($qty < 0.5) {
            $errors[] = 'Minimum reservation is 0.5 kg.';
        }
        if ($qty > (float)$listing['quantity_remaining_kg']) {
            $errors[] = 'Only ' . $listing['quantity_remaining_kg']
                     . ' kg is still available.';
        }
        if ($slotId <= 0) {
            $errors[] = 'Please choose a pickup slot.';
        } elseif (!$slotModel->hasCapacity($slotId)) {
            $errors[] = 'That pickup slot is fully booked.';
        }

        if ($errors) {
            // Re-render the checkout form with errors preserved.
            $expiryDate = new \DateTime($listing['expiry_date']);
            $listing['discount_pct']    = $discount->calculateDiscountPercent($expiryDate);
            $listing['reference_price'] = (float)($listing['reference_price'] ?? 0.0);
            $listing['final_price']     = $discount->applyDiscount(
                $listing['reference_price'], $listing['discount_pct']
            );
            $this->render('consumer/checkout', [
                'title'   => 'Checkout',
                'active'  => 'marketplace',
                'crumb'   => 'Checkout',
                'listing' => $listing,
                'slots'   => $slotModel->findAvailableForListing($id),
                'errors'  => $errors,
                'input'   => $_POST,
            ]);
            return;
        }

        // ---- compute discount ----
        $expiryDate  = new \DateTime($listing['expiry_date']);
        $discountPct = $discount->calculateDiscountPercent($expiryDate);
        $unitPrice   = $discount->applyDiscount(
            (float)($listing['reference_price'] ?? 0.0), $discountPct
        );
        $totalPrice  = round($unitPrice * $qty, 2);

        // ---- write ----
        try {
            $this->db()->beginTransaction();

            $resId = $resModel->create([
                'listing_id'      => $id,
                'user_id'         => $user['id'],
                'reserved_qty_kg' => $qty,
                'reservation_type'=> 'consumer_paid',
                'discount_pct'    => $discountPct,
                'price_paid'      => $totalPrice,
                'pickup_slot_id'  => $slotId,
            ]);

            $listingModel->decrementRemaining($id, $qty);
            $slotModel->incrementBooked($slotId);

            // If remaining stock hit zero, flip listing to 'reserved'.
            $updated = $listingModel->find($id);
            if ((float)$updated['quantity_remaining_kg'] <= 0) {
                (new ListingStateMachine())
                    ->assertTransition($updated['status'], 'reserved');
                $listingModel->updateStatus($id, 'reserved');
            }

            $this->db()->commit();
        } catch (\Throwable $e) {
            $this->db()->rollBack();
            error_log($e->getMessage());
            $this->flash('error', 'Could not complete the reservation. Please try again.');
            $this->redirect(BASE_URL . '/consumer/listings/' . $id . '/checkout');
        }

        // Notify the outlet employee who posted this listing.
        if (!empty($listing['posted_by'])) {
            $notifier->notify(
                (int)$listing['posted_by'],
                'A consumer reserved ' . $qty . ' kg of "' . $listing['item_name'] . '".',
                'consumer_reservation'
            );
        }

        // Notify the consumer themselves — this shows up in /consumer/notifications.
        $notifier->notify(
            (int)$user['id'],
            'Reservation confirmed for ' . rtrim(rtrim(number_format($qty, 1), '0'), '.')
            . ' kg of "' . $listing['item_name'] . '". Pre-payment simulated: LKR '
            . number_format($totalPrice, 2) . '.',
            'reservation_confirmed'
        );

        $this->flash('success',
            'Reservation confirmed and Rs ' . number_format($totalPrice, 2)
            . ' pre-payment simulated. Show your pickup token on collection.'
        );
        // Signal to the orders page that it should auto-open the token modal
        // for this reservation, so the consumer immediately sees what to
        // present at the outlet. Consumed and cleared inside orders().
        \App\Core\Session::set('just_reserved_id', $resId);
        $this->redirect(BASE_URL . '/consumer/orders');
    }

    /* --------------------------------------------------------- */
    /* GET /consumer/orders    →  list past + current orders     */
    /* --------------------------------------------------------- */
    public function orders(): void
    {
        $user = $this->requireRole('consumer');
        $filter = $_GET['filter'] ?? 'all';    // all | active | completed
        if (!in_array($filter, ['all', 'active', 'completed'], true)) {
            $filter = 'all';
        }

        $resModel = new Reservation();
        $orders   = $resModel->findByUser((int)$user['id'], $filter);
        $totalRescued = $resModel->countCollectedByUser((int)$user['id']);

        // Consume the "just placed" signal so the modal only auto-opens once.
        $justReservedId = (int) (\App\Core\Session::get('just_reserved_id') ?? 0);
        \App\Core\Session::remove('just_reserved_id');

        $this->render('consumer/orders', [
            'title'          => 'Order History',
            'active'         => 'orders',
            'crumb'          => 'Order History',
            'orders'         => $orders,
            'filter'         => $filter,
            'totalRescued'   => $totalRescued,
            'justReservedId' => $justReservedId,
        ]);
    }

    /* --------------------------------------------------------- */
    /* POST /consumer/orders/{id}/confirm  →  UC-14              */
    /*   Consumer taps "Mark as collected" on pickup.            */
    /* --------------------------------------------------------- */
    public function confirmPickup(int $reservationId): void
    {
        $user = $this->requireRole('consumer');
        $this->verifyCsrf();

        $resModel  = new Reservation();
        $reservation = $resModel->find($reservationId);
        if (!$reservation || (int)$reservation['user_id'] !== (int)$user['id']) {
            $this->flash('error', 'Reservation not found.');
            $this->redirect(BASE_URL . '/consumer/orders');
        }
        if ($reservation['status'] !== 'active') {
            $this->flash('error', 'This reservation cannot be confirmed.');
            $this->redirect(BASE_URL . '/consumer/orders');
        }

        $resModel->markCompleted($reservationId, (float)$reservation['reserved_qty_kg']);

        // If the listing is now fully collected, transition its state.
        $listingModel = new Listing();
        $listing = $listingModel->find((int)$reservation['listing_id']);
        if ($listing && (float)$listing['quantity_remaining_kg'] <= 0) {
            (new ListingStateMachine())
                ->assertTransition($listing['status'], 'collected');
            $listingModel->updateStatus((int)$reservation['listing_id'], 'collected');
        }

        (new NotificationService())->notify(
            (int)$user['id'],
            'Pickup confirmed for "' . ($listing['item_name'] ?? 'your order')
            . '". Thanks for rescuing food!',
            'pickup_confirmed'
        );

        $this->flash('success', 'Pickup confirmed. Thank you for rescuing food!');
        $this->redirect(BASE_URL . '/consumer/orders');
    }

    /* --------------------------------------------------------- */
    /* POST /consumer/orders/{id}/cancel  →  Delete/cancel       */
    /* --------------------------------------------------------- */
    public function cancelReservation(int $reservationId): void
    {
        $user = $this->requireRole('consumer');
        $this->verifyCsrf();

        $resModel   = new Reservation();
        $listingMdl = new Listing();
        $slotMdl    = new PickupSlot();

        $reservation = $resModel->find($reservationId);
        if (!$reservation || (int)$reservation['user_id'] !== (int)$user['id']) {
            $this->flash('error', 'Reservation not found.');
            $this->redirect(BASE_URL . '/consumer/orders');
        }
        if ($reservation['status'] !== 'active') {
            $this->flash('error', 'Only active reservations can be cancelled.');
            $this->redirect(BASE_URL . '/consumer/orders');
        }

        try {
            $this->db()->beginTransaction();
            $resModel->markCancelled($reservationId);
            $listingMdl->incrementRemaining(
                (int)$reservation['listing_id'],
                (float)$reservation['reserved_qty_kg']
            );
            if (!empty($reservation['pickup_slot_id'])) {
                $slotMdl->decrementBooked((int)$reservation['pickup_slot_id']);
            }
            // Re-open the listing if it was closed.
            $listing = $listingMdl->find((int)$reservation['listing_id']);
            if ($listing && $listing['status'] === 'reserved'
                && (float)$listing['quantity_remaining_kg'] > 0) {
                $listingMdl->updateStatus(
                    (int)$reservation['listing_id'], 'available'
                );
            }
            $this->db()->commit();
        } catch (\Throwable $e) {
            $this->db()->rollBack();
            error_log($e->getMessage());
            $this->flash('error', 'Could not cancel the reservation.');
            $this->redirect(BASE_URL . '/consumer/orders');
        }

        $listingRow = $listingMdl->find((int)$reservation['listing_id']);
        (new NotificationService())->notify(
            (int)$user['id'],
            'Reservation cancelled for "' . ($listingRow['item_name'] ?? 'your order')
            . '". The items are back on the marketplace.',
            'reservation_cancelled'
        );

        $this->flash('success', 'Reservation cancelled.');
        $this->redirect(BASE_URL . '/consumer/orders');
    }

    /* --------------------------------------------------------- */
    /* GET /consumer/notifications   →  In-app alerts inbox      */
    /*   Shows every notification for the signed-in consumer,    */
    /*   newest first, and marks unread ones as read on view.    */
    /* --------------------------------------------------------- */
    public function notifications(): void
    {
        $user = $this->requireRole('consumer');

        $model  = new Notification();
        $rows   = $model->findByUser((int)$user['id']);
        $unread = 0;
        foreach ($rows as $r) {
            if ((int)$r['is_read'] === 0) $unread++;
        }
        // Mark everything read after we captured the "unread when opened" count,
        // so the badge on this render still reflects what arrived since last visit.
        if ($unread > 0) {
            $model->markAllReadForUser((int)$user['id']);
        }

        $this->render('consumer/notifications', [
            'title'         => 'Notifications',
            'active'        => 'notifications',
            'crumb'         => 'Notifications',
            'notifications' => $rows,
            'unreadCount'   => $unread,
        ]);
    }

    /* --------------------------------------------------------- */
    /* GET /consumer/profile   →  Show profile (view + edit forms) */
    /* --------------------------------------------------------- */
    public function profile(): void
    {
        $sessionUser = $this->requireRole('consumer');
        $userRow = \User::find((int) $sessionUser['id']);

        $old      = \App\Core\Session::get('profile_form_old')     ?? [];
        $errors   = \App\Core\Session::get('profile_form_errors')  ?? [];
        $pwErrors = \App\Core\Session::get('password_form_errors') ?? [];
        \App\Core\Session::remove('profile_form_old');
        \App\Core\Session::remove('profile_form_errors');
        \App\Core\Session::remove('password_form_errors');

        $this->render('consumer/profile', [
            'title'    => 'Profile',
            'active'   => 'profile',
            'crumb'    => 'Profile',
            'userRow'  => $userRow ?: [],
            'old'      => $old,
            'errors'   => $errors,
            'pwErrors' => $pwErrors,
            'csrf'     => $this->csrfToken(),
        ]);
    }

    /* --------------------------------------------------------- */
    /* POST /consumer/profile   →  Update contact info            */
    /* --------------------------------------------------------- */
    public function updateProfile(): void
    {
        $sessionUser = $this->requireRole('consumer');
        $this->verifyCsrf();

        $input = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'phone'     => trim($_POST['phone']     ?? ''),
        ];

        $errors = [];
        if ($input['full_name'] === '') {
            $errors['full_name'] = 'Full name is required.';
        }

        if ($errors) {
            \App\Core\Session::set('profile_form_old', $input);
            \App\Core\Session::set('profile_form_errors', $errors);
            $this->redirect(BASE_URL . '/consumer/profile');
        }

        \User::updateContact((int) $sessionUser['id'], [
            'full_name' => $input['full_name'],
            'phone'     => $input['phone'] ?: null,
        ]);

        $this->flash('success', 'Profile updated.');
        $this->redirect(BASE_URL . '/consumer/profile');
    }

    /* --------------------------------------------------------- */
    /* POST /consumer/profile/password   →  Change password       */
    /* --------------------------------------------------------- */
    public function changePassword(): void
    {
        $sessionUser = $this->requireRole('consumer');
        $this->verifyCsrf();

        $current = (string) ($_POST['current_password'] ?? '');
        $next    = (string) ($_POST['new_password']     ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        $errors = [];
        $row = \User::find((int) $sessionUser['id']);
        if (!$row || !password_verify($current, $row['password_hash'])) {
            $errors['current_password'] = 'Current password is incorrect.';
        }
        if (strlen($next) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters.';
        }
        if ($next !== $confirm) {
            $errors['confirm_password'] = 'New password and confirmation do not match.';
        }

        if ($errors) {
            \App\Core\Session::set('password_form_errors', $errors);
            $this->redirect(BASE_URL . '/consumer/profile#password');
        }

        \User::updatePassword((int) $sessionUser['id'], password_hash($next, PASSWORD_DEFAULT));
        $this->flash('success', 'Password changed.');
        $this->redirect(BASE_URL . '/consumer/profile');
    }

    /* --------------------------------------------------------- */
    /* Helpers                                                   */
    /* --------------------------------------------------------- */
    private function db(): \PDO
    {
        return \App\Core\Database::connect();
    }

    // Mirrors EmployeeController::imageFor so the marketplace card
    // shows the same picture the employee sees for the same item.
    private function imageFor(string $itemName, string $category): string
    {
        $base = BASE_URL . '/assets/images/';
        $name = strtolower($itemName);

        $keywordMap = [
            'banana'      => 'bananas.jpg',
            'plantain'    => 'bananas.jpg',
            'bell pepper' => 'bell-peppers.jpg',
            'capsicum'    => 'bell-peppers.jpg',
            'pepper'      => 'bell-peppers.jpg',
            'broccoli'    => 'broccoli.jpg',
            'carrot'      => 'carrots.jpg',
            'strawberr'   => 'strawberries.jpg',
            'berry'       => 'strawberries.jpg',
        ];
        foreach ($keywordMap as $needle => $file) {
            if (strpos($name, $needle) !== false) {
                return $base . $file;
            }
        }

        return $base . 'produce-crate.jpg';
    }
}
