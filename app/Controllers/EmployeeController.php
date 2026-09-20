<?php
/**
 * app/Controllers/EmployeeController.php
 * dashboard() below uses hardcoded sample data matching the mockup, just
 * to prove the routing -> controller -> view -> layout chain works.
 * Swap the sample arrays for real Model calls once Listing.php / Outlet.php
 * have query methods (see comment block at the top of Views/employee/dashboard.php).
 */
 
require_once __DIR__ . '/BaseController.php';
 
class EmployeeController extends BaseController
{
    public function dashboard(): void
    {
        $user = Auth::user();
        $outlet = $user ? Outlet::findByUserId($user->id) : null;
        $rows = $outlet ? Listing::forOutlet((int) $outlet['id']) : [];

        // With the new "today only" listing rule, status is purely date-based:
        // today's listing → active, any older listing → expired. Reserved /
        // expiring badges no longer apply.
        $today = date('Y-m-d');
        $allInventory = array_map(function ($r) use ($today) {
            $viewStatus = ($r['expiry_date'] === $today) ? 'active' : 'expired';

            $totalKg      = (float) $r['quantity_kg'];
            $remainingKg  = (float) ($r['quantity_remaining_kg'] ?? $r['quantity_kg']);
            $reservedKg   = max(0.0, $totalKg - $remainingKg);
            $fmt = fn(float $n): string => rtrim(rtrim(number_format($n, 1, '.', ''), '0'), '.');

            return [
                'id'           => (int) $r['id'],
                'image'        => $this->imageFor($r['item_name'], $r['category']),
                'name'         => $r['item_name'],
                'category'     => ucfirst($r['category']) . 's',
                'sku'          => 'LST-' . str_pad((string) $r['id'], 4, '0', STR_PAD_LEFT),
                'quantity'     => $fmt($remainingKg) . ' / ' . $fmt($totalKg) . ' kg',
                'reserved_kg'  => $reservedKg,
                'remaining_kg' => $remainingKg,
                'total_kg'     => $totalKg,
                'expires'      => date('n/j/Y', strtotime($r['expiry_date'])),
                'status'       => $viewStatus,
            ];
        }, $rows);

        $activeCount  = count(array_filter($allInventory, fn($i) => $i['status'] === 'active'));
        $expiredCount = count(array_filter($allInventory, fn($i) => $i['status'] === 'expired'));
        $totalKg = array_sum(array_map(fn($r) => (float) $r['quantity_kg'], $rows));

        $stats = [
            'active_listings' => $activeCount,
            'total_rescued'   => number_format($totalKg, 1) . ' kg',
            'expiring_soon'   => $expiredCount,
            'collection_rate' => count($allInventory) > 0 ? '—' : '0%',
        ];
 
        // --- Filters & sort, read from the query string ---
        // TODO: once Listing.php exists, replace the array_filter/usort below
        // with real WHERE/ORDER BY clauses — the $filters shape stays the same.
        $filters = [
            'q'        => trim($_GET['q'] ?? ''),
            'category' => trim($_GET['category'] ?? ''),
            'status'   => trim($_GET['status'] ?? ''),
            'sort'     => trim($_GET['sort'] ?? 'name_asc'),
        ];
 
        $categoryOptions = array_values(array_unique(array_column($allInventory, 'category')));
        sort($categoryOptions);
 
        $inventory = array_filter($allInventory, function ($item) use ($filters) {
            if ($filters['q'] !== '') {
                $haystack = strtolower($item['name'] . ' ' . $item['sku']);
                if (strpos($haystack, strtolower($filters['q'])) === false) {
                    return false;
                }
            }
            if ($filters['category'] !== '' && $item['category'] !== $filters['category']) {
                return false;
            }
            if ($filters['status'] !== '' && $item['status'] !== $filters['status']) {
                return false;
            }
            return true;
        });
 
        usort($inventory, function ($a, $b) use ($filters) {
            switch ($filters['sort']) {
                case 'expiry_asc':
                    return strtotime($a['expires']) <=> strtotime($b['expires']);
                case 'qty_desc':
                    return $b['remaining_kg'] <=> $a['remaining_kg'];
                case 'qty_asc':
                    return $a['remaining_kg'] <=> $b['remaining_kg'];
                case 'name_asc':
                default:
                    return strcasecmp($a['name'], $b['name']);
            }
        });
 
        $inventory = array_values($inventory);
 
        $communityImpact = [
            'food_saved'        => '4,250 kg',
            'charities_served'  => 12,
        ];
 
        $alerts = [
            [
                'type' => 'danger',
                'title' => 'Expired: Sweet Bell Peppers',
                'meta' => 'SKU VEG-BEL-005 · 5 units',
                'action_label' => 'Clear Listing',
                'action_href' => BASE_URL . '/employee/listings/5',
            ],
            [
                'type' => 'warning',
                'title' => 'Expiring Today: Ripe Bananas',
                'meta' => '20 units remaining · Donate now',
                'action_label' => 'Push to Charity',
                'action_href' => BASE_URL . '/employee/listings/2/push',
            ],
        ];
 
        $highlight = [
            'id' => 6,
            'image' => $this->imageFor('Fresh Mixed Vegetable Crate', 'vegetable'),
            'badge' => 'Available',
            'title' => 'Fresh Mixed Vegetable Crate',
            'category' => 'Vegetables',
            'location' => 'Shelf A-12',
            'expires' => '4 hours',
            'quantity_label' => '12 units',
        ];
 
        $activeListingsTotal = $stats['active_listings'];
 
        // 1. Render the inner view into a buffer. This also sets
        //    $pageTitle / $pageSubtitle / $breadcrumbs / $activeRoute /
        //    $pageActions / $extraStylesheets, which the layout below reads.
        ob_start();
        require __DIR__ . '/../Views/employee/dashboard.php';
        $content = ob_get_clean();
 
        // 2. Wrap it in the shared layout (sidebar, topbar, page header).
        require __DIR__ . '/../Views/layouts/main.php';
    }
 
    // Pick the best image in public/assets/images/ for a listing.
    // Tries an item-name keyword match first (bananas, broccoli, ...),
    // then a category-wide default, then a generic crate.
    private function imageFor(string $itemName, string $category): string
    {
        $base = BASE_URL . '/assets/images/';
        $name = strtolower($itemName);

        // Order matters: more specific keywords first (e.g. "bell pepper"
        // before "pepper") so we don't lose the exact match to a broader one.
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

    private function listingCategories(): array
    {
        // Shared between createListing() (renders the <select>) and
        // storeListing() (validates the submitted value against it).
        // NOTE: replace with your actual category list if you've since
        // customized this (e.g. fruits/vegetables only) — keep both call
        // sites in sync.
        return [
            'fruits'     => 'Fruits',
            'vegetables' => 'Vegetables',
        ];
    }
 
    public function createListing(): void
    {
        if ((int) date('H') * 60 + (int) date('i') >= 19 * 60) {
            Session::flash('error', 'The 7:00 PM posting deadline has passed. New listings resume tomorrow.');
            header('Location: ' . BASE_URL . '/employee/dashboard');
            exit;
        }

        $categories = $this->listingCategories();
 
        // Repopulate the form after a failed POST (see storeListing()),
        // or start blank on a normal GET visit.
        $old    = Session::get('listing_form_old', []);
        $errors = Session::get('listing_form_errors', []);
        Session::forget('listing_form_old');
        Session::forget('listing_form_errors');
 
        ob_start();
        require __DIR__ . '/../Views/employee/create_listing.php';
        $content = ob_get_clean();
 
        require __DIR__ . '/../Views/layouts/main.php';
    }
 
    public function storeListing(): void
    {
        // Standard: all listings must be posted before the 7:00 PM charity
        // priority window begins. After 7 PM, block new listings entirely.
        if ((int) date('H') * 60 + (int) date('i') >= 19 * 60) {
            Session::flash('error', 'The 7:00 PM posting deadline has passed. New listings resume tomorrow.');
            header('Location: ' . BASE_URL . '/employee/dashboard');
            exit;
        }

        // Standard rule: listings are for today only, and the claim deadline
        // is fixed to the store's close time (10:30 PM). Ignore any date/time
        // posted from the form — we no longer accept those from the user.
        $input = [
            'listing_title'    => trim($_POST['listing_title'] ?? ''),
            'category'         => trim($_POST['category'] ?? ''),
            'quantity'         => trim($_POST['quantity'] ?? ''),
            'reference_price'  => trim($_POST['reference_price'] ?? ''),
            'best_before_date' => date('Y-m-d'),
            'best_before_time' => '22:30',
            'pickup_location'  => '-',
        ];

        $errors = $this->validateListing($input);

        if (!empty($errors)) {
            Session::set('listing_form_old', $input);
            Session::set('listing_form_errors', $errors);
            header('Location: ' . BASE_URL . '/employee/listings/create');
            exit;
        }

        $user = Auth::user();
        $outlet = $user ? Outlet::findByUserId($user->id) : null;
        if (!$user || !$outlet) {
            Session::flash('error', 'Your account is not linked to an outlet. Log in as a supermarket user.');
            header('Location: ' . BASE_URL . '/employee/dashboard');
            exit;
        }

        preg_match('/(\d+(?:\.\d+)?)/', $input['quantity'], $m);
        $quantityKg = (float) ($m[1] ?? 0);
        $categoryDb = $input['category'] === 'fruits' ? 'fruit' : 'vegetable';
        $referencePrice = (float) ($input['reference_price'] ?? 0);

        $listingId = Listing::create([
            'outlet_id'       => (int) $outlet['id'],
            'posted_by'       => $user->id,
            'item_name'       => $input['listing_title'],
            'category'        => $categoryDb,
            'quantity_kg'     => $quantityKg,
            'reference_price' => $referencePrice,
            'expiry_date'     => $input['best_before_date'],
            'claim_deadline'  => $input['best_before_date'] . ' ' . $input['best_before_time'] . ':00',
        ]);

        $this->generatePickupSlots($listingId);

        $expiresLabel = date(
            'M j, g:i A',
            strtotime($input['best_before_date'] . ' ' . $input['best_before_time'])
        );

        $listing = [
            'id'              => $listingId,
            'title'           => $input['listing_title'],
            'category'        => $this->listingCategories()[$input['category']] ?? $input['category'],
            'quantity'        => $input['quantity'],
            'expires_label'   => $expiresLabel,
            'pickup_location' => $input['pickup_location'],
            'listing_ref'     => 'LST-' . str_pad((string) $listingId, 4, '0', STR_PAD_LEFT),
            'image'           => $this->imageFor($input['listing_title'], $categoryDb),
        ];

        Session::set('just_published', $listing);
        header('Location: ' . BASE_URL . '/employee/listings/published');
        exit;
    }
 
    public function editListing(string $id): void
    {
        $id = (int) $id;
        $listing = Listing::find($id);
        if (!$listing) {
            Session::flash('error', 'Listing not found.');
            header('Location: ' . BASE_URL . '/employee/dashboard');
            exit;
        }

        $categories = $this->listingCategories();

        $old = Session::get('listing_form_old', [
            'listing_title'    => $listing['item_name'],
            'category'         => $listing['category'] === 'fruit' ? 'fruits' : 'vegetables',
            'quantity'         => rtrim(rtrim($listing['quantity_kg'], '0'), '.'),
            'reference_price'  => rtrim(rtrim($listing['reference_price'] ?? '0', '0'), '.'),
            'best_before_date' => $listing['expiry_date'],
            'best_before_time' => date('H:i', strtotime($listing['claim_deadline'])),
            'pickup_location'  => '-',
        ]);
        $errors = Session::get('listing_form_errors', []);
        Session::forget('listing_form_old');
        Session::forget('listing_form_errors');

        $image = $this->imageFor($listing['item_name'], $listing['category']);
        $imageAlt = $listing['item_name'];

        ob_start();
        require __DIR__ . '/../Views/employee/edit_listing.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function updateListing(string $id): void
    {
        $id = (int) $id;
        $listing = Listing::find($id);
        if (!$listing) {
            header('Location: ' . BASE_URL . '/employee/dashboard');
            exit;
        }

        // Same "today only" rule as storeListing: date + deadline are not
        // user-editable; the deadline is pinned to today 22:30.
        $input = [
            'listing_title'    => trim($_POST['listing_title'] ?? ''),
            'category'         => trim($_POST['category'] ?? ''),
            'quantity'         => trim($_POST['quantity'] ?? ''),
            'reference_price'  => trim($_POST['reference_price'] ?? ''),
            'best_before_date' => date('Y-m-d'),
            'best_before_time' => '22:30',
            'pickup_location'  => trim($_POST['pickup_location'] ?? '-'),
        ];

        $errors = $this->validateListing($input);
        if (!empty($errors)) {
            Session::set('listing_form_old', $input);
            Session::set('listing_form_errors', $errors);
            header('Location: ' . BASE_URL . '/employee/listings/' . $id . '/edit');
            exit;
        }

        preg_match('/(\d+(?:\.\d+)?)/', $input['quantity'], $m);
        $quantityKg = (float) ($m[1] ?? 0);
        $categoryDb = $input['category'] === 'fruits' ? 'fruit' : 'vegetable';
        $referencePrice = (float) ($input['reference_price'] ?? 0);

        Listing::update($id, [
            'item_name'       => $input['listing_title'],
            'category'        => $categoryDb,
            'quantity_kg'     => $quantityKg,
            'reference_price' => $referencePrice,
            'expiry_date'     => $input['best_before_date'],
            'claim_deadline'  => $input['best_before_date'] . ' ' . $input['best_before_time'] . ':00',
        ]);

        Session::flash('success', 'Listing updated.');
        header('Location: ' . BASE_URL . '/employee/dashboard');
        exit;
    }

    public function deleteListing(string $id): void
    {
        Listing::delete((int) $id);
        Session::flash('success', 'Listing deleted.');
        header('Location: ' . BASE_URL . '/employee/dashboard');
        exit;
    }

    private function validateListing(array $input): array
    {
        $errors = [];
 
        if ($input['listing_title'] === '') {
            $errors['listing_title'] = 'Listing title is required.';
        }
 
        if ($input['category'] === '' || !isset($this->listingCategories()[$input['category']])) {
            $errors['category'] = 'Please select a valid category.';
        }
 
        if ($input['quantity'] === '') {
            $errors['quantity'] = 'Quantity is required.';
        }

        if (!isset($input['reference_price']) || $input['reference_price'] === '') {
            // Blank is allowed; treat as free/donation-only (price stays 0).
        } elseif (!is_numeric($input['reference_price']) || (float) $input['reference_price'] < 0) {
            $errors['reference_price'] = 'Reference price must be zero or a positive number.';
        }

        if ($input['pickup_location'] === '') {
            $errors['pickup_location'] = 'Pickup location is required.';
        }
 
        // --- Best Before / Expiry: date + time window validation ---
        // Mirrors the client-side rules in create-listing.js: today through
        // 2 days out, 9:00 AM-10:00 PM, and not in the past if today.
        if ($input['best_before_date'] === '' || $input['best_before_time'] === '') {
            $errors['best_before'] = 'Best before date and time are required.';
        } else {
            $date = DateTime::createFromFormat('Y-m-d', $input['best_before_date']);
            $time = DateTime::createFromFormat('H:i', $input['best_before_time']);
 
            if (!$date || !$time) {
                $errors['best_before'] = 'Enter a valid date and time.';
            } else {
                $today   = new DateTime('today');
                $maxDate = (clone $today)->modify('+2 days');
 
                if ($date < $today || $date > $maxDate) {
                    $errors['best_before'] = 'Date must be today, tomorrow, or the day after.';
                } elseif ($input['best_before_time'] < '09:00' || $input['best_before_time'] > '22:30') {
                    $errors['best_before'] = 'Time must be between 9:00 AM and 10:30 PM.';
                } elseif ($date == $today && $input['best_before_time'] < date('H:i')) {
                    $errors['best_before'] = 'Time cannot be in the past.';
                }
            }
        }
 
        return $errors;
    }
 
    /**
     * Generate the pickup-slot grid for a newly created listing.
     *
     * Every listing gets a fixed grid of seven 30-minute slots from
     * 7:00 PM through 10:30 PM (store close):
     *   19:00-19:30, 19:30-20:00, 20:00-20:30  (charity priority window)
     *   20:30-21:00, 21:00-21:30, 21:30-22:00, 22:00-22:30  (both charities + consumers)
     *
     * Slot generation runs when the listing is created (before 7 PM per the
     * daily posting cutoff), so the whole grid is always in the future.
     * Capacity defaults to 1 per slot.
     */
    private function generatePickupSlots(int $listingId): void
    {
        $firstSlot = (new DateTime())->setTime(19, 0, 0);
        $lastSlot  = (new DateTime())->setTime(22, 0, 0);

        $db   = Database::connection();
        $stmt = $db->prepare(
            'INSERT INTO pickup_slots (listing_id, slot_start, slot_end, capacity, booked_count)
             VALUES (:listing_id, :start, :end, 1, 0)'
        );

        for ($slot = clone $firstSlot; $slot <= $lastSlot; $slot->modify('+30 minutes')) {
            $slotEnd = (clone $slot)->modify('+30 minutes');
            $stmt->execute([
                ':listing_id' => $listingId,
                ':start'      => $slot->format('Y-m-d H:i:s'),
                ':end'        => $slotEnd->format('Y-m-d H:i:s'),
            ]);
        }
    }

    private function outletIdForCurrentUser(): int
    {
        $user   = Auth::user();
        $outlet = $user ? Outlet::findByUserId($user->id) : null;
        return $outlet ? (int) $outlet['id'] : 0;
    }
 
    public function pickupVerification(): void
    {
        $pendingPickup = Session::get('verified_pickup');
        $tokenError    = Session::get('token_error');
        $oldToken      = Session::get('token_old', '');
        Session::forget('token_error');
        Session::forget('token_old');

        $user   = Auth::user();
        $outlet = $user ? Outlet::findByUserId($user->id) : null;
        $outletId = $outlet ? (int) $outlet['id'] : 0;

        $successfulToday = $outletId ? Pickup::countToday($outletId) : 0;
        $totalKgToday    = $outletId ? Pickup::sumKgToday($outletId) : 0.0;
        $weightRescued   = rtrim(rtrim(number_format($totalKgToday, 1), '0'), '.') . 'kg Rescued';
        $trendPct        = null; // no historical baseline to compute a delta against yet

        $recentRows = $outletId ? Pickup::recentForOutlet($outletId, 5) : [];
        $recentHistory = array_map(function ($row) {
            return [
                'order_id'  => 'ORD-' . str_pad((string) $row['reservation_id'], 4, '0', STR_PAD_LEFT),
                'collector' => $row['org_name'] ?: ($row['user_name'] . ' (Individual)'),
                'time'      => date('g:i A', strtotime($row['confirmed_at'])),
                'weight_kg' => rtrim(rtrim(number_format((float) $row['collected_qty_kg'], 1), '0'), '.'),
            ];
        }, $recentRows);

        ob_start();
        require __DIR__ . '/../Views/employee/pickup_verification.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
 
    public function verifyPickupToken(): void
    {
        $token    = strtoupper(trim($_POST['token'] ?? ''));
        $outletId = $this->outletIdForCurrentUser();

        if ($token === '') {
            Session::set('token_error', 'Enter a token to continue.');
            header('Location: ' . BASE_URL . '/employee/pickups/verify');
            exit;
        }

        if ($outletId === 0) {
            Session::set('token_error', 'Your account is not linked to an outlet — cannot verify pickups.');
            Session::set('token_old', $token);
            header('Location: ' . BASE_URL . '/employee/pickups/verify');
            exit;
        }

        $reservation = Reservation::findByTokenForOutlet($outletId, $token);
        if (!$reservation) {
            Session::set('token_error', 'That token was not recognized (or the reservation is not for this outlet).');
            Session::set('token_old', $token);
            header('Location: ' . BASE_URL . '/employee/pickups/verify');
            exit;
        }

        // Human-friendly labels for the confirmation panel
        $orderId   = 'ORD-' . str_pad((string) $reservation['id'], 5, '0', STR_PAD_LEFT);
        $collector = !empty($reservation['charity_org'])
            ? $reservation['charity_org']
            : ($reservation['collector_name'] . ' (Individual)');

        Session::set('verified_pickup', [
            'reservation_id'   => (int) $reservation['id'],
            'order_id'         => $orderId,
            'collector'        => $collector,
            'item_name'        => $reservation['item_name'],
            'reserved_qty_kg'  => (float) $reservation['reserved_qty_kg'],
            'image'            => $this->imageFor(
                                     $reservation['item_name'],
                                     $reservation['category'] ?? ''
                                  ),
        ]);

        header('Location: ' . BASE_URL . '/employee/pickups/verify');
        exit;
    }

    public function resetPickupVerification(): void
    {
        Session::forget('verified_pickup');
        header('Location: ' . BASE_URL . '/employee/pickups/verify');
        exit;
    }

    public function completePickup(): void
    {
        $pendingPickup  = Session::get('verified_pickup');
        $postedResId    = (int) ($_POST['reservation_id'] ?? 0);

        // Guard against a stale/tampered form post that doesn't match what
        // was actually verified.
        if (!$pendingPickup || (int) $pendingPickup['reservation_id'] !== $postedResId) {
            header('Location: ' . BASE_URL . '/employee/pickups/verify');
            exit;
        }

        // Re-load the reservation and revalidate that it still belongs to this
        // outlet and is still active. (State could have changed since verify.)
        $outletId    = $this->outletIdForCurrentUser();
        $reservation = Reservation::find($postedResId);
        $user        = Auth::user();

        if (!$reservation || !$user || $outletId === 0) {
            Session::flash('error', 'Could not complete pickup — session or account state changed.');
            Session::forget('verified_pickup');
            header('Location: ' . BASE_URL . '/employee/pickups/verify');
            exit;
        }

        $listing = Listing::find((int) $reservation['listing_id']);
        if (!$listing || (int) $listing['outlet_id'] !== $outletId) {
            Session::flash('error', 'This reservation is not for your outlet.');
            Session::forget('verified_pickup');
            header('Location: ' . BASE_URL . '/employee/pickups/verify');
            exit;
        }

        if ($reservation['status'] !== 'active') {
            Session::flash('error', 'That reservation is no longer active.');
            Session::forget('verified_pickup');
            header('Location: ' . BASE_URL . '/employee/pickups/verify');
            exit;
        }

        $qty = (float) $reservation['reserved_qty_kg'];
        $db  = Database::connection();

        try {
            $db->beginTransaction();

            Reservation::markCompleted($postedResId);
            Pickup::create($postedResId, $qty, (int) $user->id);

            // If the listing's remaining stock has now hit zero, flip its state.
            $refreshed = Listing::find((int) $reservation['listing_id']);
            if ($refreshed && (float) $refreshed['quantity_remaining_kg'] <= 0
                && $refreshed['status'] !== 'collected') {
                $db->prepare("UPDATE listings SET status = 'collected' WHERE id = :id")
                   ->execute([':id' => (int) $reservation['listing_id']]);
            }

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            error_log($e->getMessage());
            Session::flash('error', 'Could not complete pickup. Please try again.');
            header('Location: ' . BASE_URL . '/employee/pickups/verify');
            exit;
        }

        // Notify the reserver (consumer or charity) that their token was
        // processed at the outlet. The row lands in their in-app inbox
        // (Consumer sees it at /consumer/notifications).
        (new \App\Services\NotificationService())->notify(
            (int) $reservation['user_id'],
            'Pickup completed at the outlet for "' . $pendingPickup['item_name']
            . '" (' . rtrim(rtrim(number_format($qty, 1), '0'), '.')
            . ' kg). Thanks for rescuing food!',
            'pickup_completed_by_outlet'
        );

        Session::forget('verified_pickup');
        Session::flash('success', 'Pickup for ' . $pendingPickup['collector'] . ' marked complete.');
        header('Location: ' . BASE_URL . '/employee/pickups/verify');
        exit;
    }
 
    public function profile(): void
    {
        $user   = Auth::user();
        if (!$user) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $userRow = User::find($user->id);
        $outlet  = Outlet::findByUserId($user->id);

        $old    = Session::get('profile_form_old', []);
        $errors = Session::get('profile_form_errors', []);
        Session::forget('profile_form_old');
        Session::forget('profile_form_errors');

        $pwErrors = Session::get('password_form_errors', []);
        Session::forget('password_form_errors');

        $pageTitle    = 'Profile Settings';
        $pageSubtitle = 'Manage your outlet details and account credentials.';
        $breadcrumbs  = ['Supermarket Staff', 'Profile'];
        $activeRoute  = 'employee.profile';

        ob_start();
        require __DIR__ . '/../Views/employee/profile.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function updateProfile(): void
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $input = [
            'full_name'       => trim($_POST['full_name']       ?? ''),
            'phone'           => trim($_POST['phone']           ?? ''),
            'outlet_name'     => trim($_POST['outlet_name']     ?? ''),
            'branch_location' => trim($_POST['branch_location'] ?? ''),
            'region'          => trim($_POST['region']          ?? ''),
        ];

        $errors = [];
        if ($input['full_name']       === '') $errors['full_name']       = 'Contact person name is required.';
        if ($input['outlet_name']     === '') $errors['outlet_name']     = 'Outlet name is required.';
        if ($input['branch_location'] === '') $errors['branch_location'] = 'Branch location is required.';
        if ($input['region']          === '') $errors['region']          = 'Region is required.';

        if ($errors) {
            Session::set('profile_form_old', $input);
            Session::set('profile_form_errors', $errors);
            header('Location: ' . BASE_URL . '/employee/profile');
            exit;
        }

        User::updateContact($user->id, [
            'full_name' => $input['full_name'],
            'phone'     => $input['phone'] ?: null,
        ]);

        $outlet = Outlet::findByUserId($user->id);
        if ($outlet) {
            Outlet::updateProfile((int) $outlet['id'], [
                'outlet_name'     => $input['outlet_name'],
                'branch_location' => $input['branch_location'],
                'region'          => $input['region'],
            ]);
        }

        Session::flash('success', 'Profile updated.');
        header('Location: ' . BASE_URL . '/employee/profile');
        exit;
    }

    public function changePassword(): void
    {
        $user = Auth::user();
        if (!$user) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $current = (string) ($_POST['current_password'] ?? '');
        $next    = (string) ($_POST['new_password']     ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        $errors = [];
        $row = User::find($user->id);
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
            Session::set('password_form_errors', $errors);
            header('Location: ' . BASE_URL . '/employee/profile#password');
            exit;
        }

        User::updatePassword($user->id, password_hash($next, PASSWORD_DEFAULT));
        Session::flash('success', 'Password changed.');
        header('Location: ' . BASE_URL . '/employee/profile');
        exit;
    }

    public function listingPublished(): void
    {
        // Normal flow: storeListing() redirects here after a successful
        // save, with the real listing stashed in session. Falling back to
        // sample data below only so this route still previews standalone.
        $listing = Session::get('just_published');
        Session::forget('just_published');
 
        $listing = $listing ?? [
            'id'              => 42,
            'title'           => 'Gala Apples (Case of 24)',
            'category'        => 'Fruits',
            'quantity'        => '5 Crates / 20kg',
            'expires_label'   => 'Today, 8:00 PM',
            'pickup_location' => 'Loading Dock B, South Entrance',
            'listing_ref'     => 'LST-1042',
            'image'           => $this->imageFor('Gala Apples', 'fruit'),
        ];
 
        ob_start();
        require __DIR__ . '/../Views/employee/listing_published.php';
        $content = ob_get_clean();
 
        require __DIR__ . '/../Views/layouts/main.php';
    }
}