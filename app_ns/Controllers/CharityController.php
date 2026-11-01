<?php
namespace App\Controllers;

use App\Models\Listing;
use App\Models\Reservation;
use App\Models\PickupSlot;
use App\Models\Notification;
use App\Services\PriorityWindowService;
use App\Services\ListingStateMachine;
use App\Services\NotificationService;

/**
 * CharityController: the charity side of the marketplace.
 *
 * Mirrors the consumer flow (browse -> reserve -> pickup token) with the
 * charity rules:
 *   - reservations are free (reservation_type 'charity_priority', 0 paid)
 *   - charities can book the whole 7:00 - 10:30 PM slot grid, including the
 *     7:00 - 8:30 PM priority slots consumers can't see
 *   - the feed shows a live countdown for the priority window
 *
 * Pickups are verified by outlet staff with the same HRV- token as
 * consumer orders (EmployeeController::pickupVerification).
 */
class CharityController extends BaseController
{
    /* GET /charity/dashboard */
    public function dashboard(): void
    {
        $this->requireRole('charity');
        $this->redirect(BASE_URL . '/charity/listings');
    }

    /* GET /charity/listings: Reservation Feed */
    public function browse(): void
    {
        $user = $this->requireRole('charity');

        $category = $_GET['category'] ?? 'all';
        if (!in_array($category, ['all', 'fruit', 'vegetable'], true)) {
            $category = 'all';
        }

        $address  = trim($_GET['address'] ?? '');
        $lat      = isset($_GET['lat']) && $_GET['lat'] !== '' ? (float) $_GET['lat'] : null;
        $lng      = isset($_GET['lng']) && $_GET['lng'] !== '' ? (float) $_GET['lng'] : null;
        $radiusKm = 30;
        $selectedOutletId = isset($_GET['outlet_id']) && $_GET['outlet_id'] !== ''
            ? (int) $_GET['outlet_id']
            : null;

        $nearbyOutlets = [];
        $outletIds     = null;
        if ($lat !== null && $lng !== null) {
            $nearbyOutlets = (new \App\Models\Outlet())->findNearby($lat, $lng, $radiusKm);
            $nearbyIds     = array_column($nearbyOutlets, 'id');
            if ($selectedOutletId !== null && in_array($selectedOutletId, $nearbyIds, true)) {
                $outletIds = [$selectedOutletId];
            } else {
                $selectedOutletId = null;
                $outletIds        = $nearbyIds;
            }
        }

        $listings = array_map(function (array $row) {
            $row['image'] = $this->imageFor($row['item_name'], $row['category']);
            return $row;
        }, (new Listing())->findUnclaimedForConsumers($category, $outletIds));

        // Tomorrow's listings (posted after 7 PM): shown, but not reservable yet.
        $upcoming = array_map(function (array $row) {
            $row['image'] = $this->imageFor($row['item_name'], $row['category']);
            return $row;
        }, (new Listing())->findUnclaimedForConsumers($category, $outletIds, date('Y-m-d', strtotime('+1 day'))));

        $resModel = new Reservation();

        $this->render('charity/browse', [
            'title'            => 'Reservation Feed',
            'active'           => 'feed',
            'crumb'            => 'Reservation Feed',
            'listings'         => $listings,
            'upcoming'         => $upcoming,
            'selectedCategory' => $category,
            'window'           => $this->priorityWindow(),
            'activeCount'      => $resModel->countActiveByUser((int) $user['id']),
            'collectedCount'   => $resModel->countCollectedByUser((int) $user['id']),
            'address'          => $address,
            'lat'              => $lat,
            'lng'              => $lng,
            'radiusKm'         => $radiusKm,
            'nearbyOutlets'    => $nearbyOutlets,
            'selectedOutletId' => $selectedOutletId,
        ]);
    }

    /* GET /charity/listings/{id}/reserve */
    public function reserve(int $id): void
    {
        $this->requireRole('charity');

        $listing = (new Listing())->find($id);
        if (!$this->isReservable($listing)) {
            $this->flash('error', 'That listing is no longer available.');
            $this->redirect(BASE_URL . '/charity/listings');
        }

        $this->renderReserveForm($listing, [], []);
    }

    /* POST /charity/listings/{id}/reserve */
    public function confirmReserve(int $id): void
    {
        $user = $this->requireRole('charity');
        $this->verifyCsrf();

        $listingModel = new Listing();
        $slotModel    = new PickupSlot();

        $listing = $listingModel->find($id);
        if (!$this->isReservable($listing)) {
            $this->flash('error', 'That listing is no longer available.');
            $this->redirect(BASE_URL . '/charity/listings');
        }

        $qty    = (float) ($_POST['quantity_kg'] ?? 0);
        $slotId = (int) ($_POST['pickup_slot_id'] ?? 0);
        $errors = [];

        if ($qty < 0.5) {
            $errors[] = 'Minimum reservation is 0.5 kg.';
        }
        if ($qty > (float) $listing['quantity_remaining_kg']) {
            $errors[] = 'Only ' . $listing['quantity_remaining_kg'] . ' kg is still available.';
        }
        $validSlotIds = array_map('intval', array_column($slotModel->findAvailableForCharity($id), 'id'));
        if ($slotId <= 0 || !in_array($slotId, $validSlotIds, true)) {
            $errors[] = 'Please choose a pickup slot.';
        }

        if ($errors) {
            $this->renderReserveForm($listing, $errors, $_POST);
            return;
        }

        try {
            $this->db()->beginTransaction();

            $resId = (new Reservation())->create([
                'listing_id'       => $id,
                'user_id'          => $user['id'],
                'reserved_qty_kg'  => $qty,
                'reservation_type' => 'charity_priority',
                'discount_pct'     => 0,
                'price_paid'       => 0,
                'pickup_slot_id'   => $slotId,
            ]);

            $listingModel->decrementRemaining($id, $qty);
            $slotModel->incrementBooked($slotId);

            $updated = $listingModel->find($id);
            if ((float) $updated['quantity_remaining_kg'] <= 0) {
                (new ListingStateMachine())->assertTransition($updated['status'], 'reserved');
                $listingModel->updateStatus($id, 'reserved');
            }

            $this->db()->commit();
        } catch (\Throwable $e) {
            $this->db()->rollBack();
            error_log($e->getMessage());
            $this->flash('error', 'Could not complete the reservation. Please try again.');
            $this->redirect(BASE_URL . '/charity/listings/' . $id . '/reserve');
        }

        $kg       = rtrim(rtrim(number_format($qty, 1), '0'), '.');
        $notifier = new NotificationService();
        $org      = $this->orgName((int) $user['id']);

        if (!empty($listing['posted_by'])) {
            $notifier->notify(
                (int) $listing['posted_by'],
                $org . ' (charity) reserved ' . $kg . ' kg of "' . $listing['item_name'] . '".',
                'charity_reservation'
            );
        }
        $notifier->notify(
            (int) $user['id'],
            'Reservation confirmed for ' . $kg . ' kg of "' . $listing['item_name'] . '". Show your pickup token at the outlet.',
            'reservation_confirmed'
        );

        $this->flash('success', 'Reservation confirmed. Show your pickup token when you collect.');
        \App\Core\Session::set('just_reserved_id', $resId);
        $this->redirect(BASE_URL . '/charity/pickups');
    }

    /* GET /charity/pickups: Pickup Scheduler */
    public function pickups(): void
    {
        $user   = $this->requireRole('charity');
        $filter = $_GET['filter'] ?? 'active';
        if (!in_array($filter, ['all', 'active', 'completed'], true)) {
            $filter = 'active';
        }

        $resModel = new Reservation();
        $justReservedId = (int) (\App\Core\Session::get('just_reserved_id') ?? 0);
        \App\Core\Session::remove('just_reserved_id');

        $this->render('charity/pickups', [
            'title'          => 'Pickup Scheduler',
            'active'         => 'pickups',
            'crumb'          => 'Pickup Scheduler',
            'orders'         => $resModel->findByUser((int) $user['id'], $filter),
            'filter'         => $filter,
            'activeCount'    => $resModel->countActiveByUser((int) $user['id']),
            'justReservedId' => $justReservedId,
        ]);
    }

    /* POST /charity/pickups/{id}/cancel */
    public function cancelReservation(int $reservationId): void
    {
        $user = $this->requireRole('charity');
        $this->verifyCsrf();

        $resModel   = new Reservation();
        $listingMdl = new Listing();

        $reservation = $resModel->find($reservationId);
        if (!$reservation || (int) $reservation['user_id'] !== (int) $user['id']) {
            $this->flash('error', 'Reservation not found.');
            $this->redirect(BASE_URL . '/charity/pickups');
        }
        if ($reservation['status'] !== 'active') {
            $this->flash('error', 'Only active reservations can be cancelled.');
            $this->redirect(BASE_URL . '/charity/pickups');
        }

        try {
            $this->db()->beginTransaction();
            $resModel->markCancelled($reservationId);
            $listingMdl->incrementRemaining((int) $reservation['listing_id'], (float) $reservation['reserved_qty_kg']);
            if (!empty($reservation['pickup_slot_id'])) {
                (new PickupSlot())->decrementBooked((int) $reservation['pickup_slot_id']);
            }
            $listing = $listingMdl->find((int) $reservation['listing_id']);
            if ($listing && $listing['status'] === 'reserved' && (float) $listing['quantity_remaining_kg'] > 0) {
                $listingMdl->updateStatus((int) $reservation['listing_id'], 'available');
            }
            $this->db()->commit();
        } catch (\Throwable $e) {
            $this->db()->rollBack();
            error_log($e->getMessage());
            $this->flash('error', 'Could not cancel the reservation.');
            $this->redirect(BASE_URL . '/charity/pickups');
        }

        $listingRow = $listingMdl->find((int) $reservation['listing_id']);
        (new NotificationService())->notify(
            (int) $user['id'],
            'Reservation cancelled for "' . ($listingRow['item_name'] ?? 'your order') . '". The items are back on the feed.',
            'reservation_cancelled'
        );

        $this->flash('success', 'Reservation cancelled.');
        $this->redirect(BASE_URL . '/charity/pickups');
    }

    /* GET /charity/notifications */
    public function notifications(): void
    {
        $user = $this->requireRole('charity');

        $model  = new Notification();
        $rows   = $model->findByUser((int) $user['id']);
        $unread = count(array_filter($rows, fn($r) => (int) $r['is_read'] === 0));
        if ($unread > 0) {
            $model->markAllReadForUser((int) $user['id']);
        }

        // Same inbox as consumers; only the empty-state link differs.
        $this->render('consumer/notifications', [
            'title'         => 'Notifications',
            'active'        => 'notifications',
            'crumb'         => 'Notifications',
            'notifications' => $rows,
            'unreadCount'   => $unread,
            'homePath'      => '/charity/listings',
            'homeLabel'     => 'Go to Reservation Feed',
        ]);
    }

    /* GET /charity/profile */
    public function profile(): void
    {
        $user = $this->requireRole('charity');

        $old      = \App\Core\Session::get('profile_form_old')     ?? [];
        $errors   = \App\Core\Session::get('profile_form_errors')  ?? [];
        $pwErrors = \App\Core\Session::get('password_form_errors') ?? [];
        \App\Core\Session::remove('profile_form_old');
        \App\Core\Session::remove('profile_form_errors');
        \App\Core\Session::remove('password_form_errors');

        $this->render('charity/profile', [
            'title'    => 'Profile',
            'active'   => 'profile',
            'crumb'    => 'Profile',
            'userRow'  => \User::find((int) $user['id']) ?: [],
            'charity'  => \Charity::findByUserId((int) $user['id']) ?: [],
            'old'      => $old,
            'errors'   => $errors,
            'pwErrors' => $pwErrors,
            'csrf'     => $this->csrfToken(),
        ]);
    }

    /* POST /charity/profile */
    public function updateProfile(): void
    {
        $user = $this->requireRole('charity');
        $this->verifyCsrf();

        $input = [
            'full_name' => trim($_POST['full_name'] ?? ''),
            'phone'     => trim($_POST['phone'] ?? ''),
        ];
        if ($input['full_name'] === '') {
            \App\Core\Session::set('profile_form_old', $input);
            \App\Core\Session::set('profile_form_errors', ['full_name' => 'Contact person is required.']);
            $this->redirect(BASE_URL . '/charity/profile');
        }

        \User::updateContact((int) $user['id'], [
            'full_name' => $input['full_name'],
            'phone'     => $input['phone'] ?: null,
        ]);

        $this->flash('success', 'Profile updated.');
        $this->redirect(BASE_URL . '/charity/profile');
    }

    /* POST /charity/profile/password */
    public function changePassword(): void
    {
        $user = $this->requireRole('charity');
        $this->verifyCsrf();

        $current = (string) ($_POST['current_password'] ?? '');
        $next    = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        $errors = [];
        $row = \User::find((int) $user['id']);
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
            $this->redirect(BASE_URL . '/charity/profile#password');
        }

        \User::updatePassword((int) $user['id'], password_hash($next, PASSWORD_DEFAULT));
        $this->flash('success', 'Password changed.');
        $this->redirect(BASE_URL . '/charity/profile');
    }

    /* --------------------------------------------------------- */
    /* Helpers                                                   */
    /* --------------------------------------------------------- */

    private function renderReserveForm(array $listing, array $errors, array $input): void
    {
        $outlet = (new \App\Models\Outlet())->find((int) $listing['outlet_id']);
        $listing['outlet_name']     = $outlet['outlet_name'] ?? 'Local outlet';
        $listing['branch_location'] = $outlet['branch_location'] ?? '';
        $listing['image']           = $this->imageFor($listing['item_name'], $listing['category']);

        $this->render('charity/reserve', [
            'title'   => 'Reserve',
            'active'  => 'feed',
            'crumb'   => 'Reserve',
            'listing' => $listing,
            'slots'   => (new PickupSlot())->findAvailableForCharity((int) $listing['id']),
            'errors'  => $errors,
            'input'   => $input,
        ]);
    }

    private function isReservable(?array $listing): bool
    {
        return $listing
            && $listing['status'] === 'available'
            && (float) $listing['quantity_remaining_kg'] > 0
            && $listing['expiry_date'] === date('Y-m-d');
    }

    /**
     * Where today's charity priority window stands, for the countdown banner.
     * @return array{state:string,start:\DateTime,end:\DateTime}
     *   state: 'upcoming' (before 7 PM) | 'open' | 'closed' (after 8:30 PM)
     */
    private function priorityWindow(): array
    {
        $now = new \DateTime();
        [$start, $end] = (new PriorityWindowService())->charityWindowBounds($now);
        $state = $now < $start ? 'upcoming' : ($now < $end ? 'open' : 'closed');
        return ['state' => $state, 'start' => $start, 'end' => $end];
    }

    private function orgName(int $userId): string
    {
        $charity = \Charity::findByUserId($userId);
        return $charity['org_name'] ?? 'A charity';
    }

    private function db(): \PDO
    {
        return \App\Core\Database::connect();
    }
}
