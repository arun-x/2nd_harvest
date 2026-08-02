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
        $stats = [
            'active_listings'  => 42,
            'total_rescued'    => '1,248 kg',
            'expiring_soon'    => 7,
            'collection_rate'  => '94%',
        ];
 
        $allInventory = [
            [
                'id' => 1, 'image' => '/assets/images/strawberries.jpg',
                'name' => 'Fresh Strawberries', 'category' => 'Fruits',
                'sku' => 'FRT-STR-001', 'quantity' => 10, 'expires' => '5/20/2024',
                'status' => 'active',
            ],
            [
                'id' => 2, 'image' => '/assets/images/bananas.jpg',
                'name' => 'Ripe Bananas', 'category' => 'Fruits',
                'sku' => 'FRT-BAN-002', 'quantity' => 20, 'expires' => '5/18/2024',
                'status' => 'expiring',
            ],
            [
                'id' => 3, 'image' => '/assets/images/carrots.jpg',
                'name' => 'Organic Carrots', 'category' => 'Vegetables',
                'sku' => 'VEG-CAR-003', 'quantity' => 30, 'expires' => '5/19/2024',
                'status' => 'active',
            ],
            [
                'id' => 4, 'image' => '/assets/images/broccoli.jpg',
                'name' => 'Broccoli Crowns', 'category' => 'Vegetables',
                'sku' => 'VEG-BRO-004', 'quantity' => 15, 'expires' => '5/17/2024',
                'status' => 'reserved',
            ],
            [
                'id' => 5, 'image' => '/assets/images/bell-peppers.jpg',
                'name' => 'Sweet Bell Peppers', 'category' => 'Vegetables',
                'sku' => 'VEG-BEL-005', 'quantity' => 5, 'expires' => '5/15/2024',
                'status' => 'expired',
            ],
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
                    return $b['quantity'] <=> $a['quantity'];
                case 'qty_asc':
                    return $a['quantity'] <=> $b['quantity'];
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
                'action_href' => '/employee/listings/5',
            ],
            [
                'type' => 'warning',
                'title' => 'Expiring Today: Ripe Bananas',
                'meta' => '20 units remaining · Donate now',
                'action_label' => 'Push to Charity',
                'action_href' => '/employee/listings/2/push',
            ],
        ];
 
        $highlight = [
            'id' => 6,
            'image' => '/assets/images/produce-crate.jpg',
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
        $input = [
            'listing_title'    => trim($_POST['listing_title'] ?? ''),
            'category'         => trim($_POST['category'] ?? ''),
            'quantity'         => trim($_POST['quantity'] ?? ''),
            'best_before_date' => trim($_POST['best_before_date'] ?? ''),
            'best_before_time' => trim($_POST['best_before_time'] ?? ''),
            'pickup_location'  => trim($_POST['pickup_location'] ?? ''),
        ];
 
        $errors = $this->validateListing($input);
 
        if (!empty($errors)) {
            Session::set('listing_form_old', $input);
            Session::set('listing_form_errors', $errors);
            header('Location: /employee/listings/create');
            exit;
        }
 
        // TODO: once Listing.php exists, replace this with a real insert:
        //   $listing = $this->listingModel->create($input + ['outlet_id' => Auth::user()->id]);
        // For now, build the summary the confirmation page expects directly
        // from the validated input.
        $expiresLabel = date(
            'M j, g:i A',
            strtotime($input['best_before_date'] . ' ' . $input['best_before_time'])
        );
 
        $listing = [
            'id'              => random_int(100, 999), // placeholder until real IDs exist
            'title'           => $input['listing_title'],
            'category'        => $this->listingCategories()[$input['category']] ?? $input['category'],
            'quantity'        => $input['quantity'],
            'expires_label'   => $expiresLabel,
            'pickup_location' => $input['pickup_location'],
            'listing_ref'     => 'LST-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
        ];
 
        Session::set('just_published', $listing);
        header('Location: /employee/listings/published');
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
                } elseif ($input['best_before_time'] < '09:00' || $input['best_before_time'] > '22:00') {
                    $errors['best_before'] = 'Time must be between 9:00 AM and 10:00 PM.';
                } elseif ($date == $today && $input['best_before_time'] < date('H:i')) {
                    $errors['best_before'] = 'Time cannot be in the past.';
                }
            }
        }
 
        return $errors;
    }
 
    private function sampleTokens(): array
    {
        // TODO: replace with a real lookup once Reservation.php/Pickup.php
        // exist — e.g. $this->reservationModel->findByToken($token).
        // Token format matches the "Reservation ID" shown in the charity's
        // Pickup Scheduler confirmation modal (e.g. "CH-77291-B").
        return [
            'CH-77291-B' => ['order_id' => 'ORD-8292', 'collector' => 'Green Valley Pantry'],
            'CH-88410-A' => ['order_id' => 'ORD-8293', 'collector' => 'Community Food Bank'],
        ];
    }
 
    public function pickupVerification(): void
    {
        $pendingPickup = Session::get('verified_pickup');
        $tokenError    = Session::get('token_error');
        $oldToken      = Session::get('token_old', '');
        Session::forget('token_error');
        Session::forget('token_old');
 
        $successfulToday = Session::get('pickups_completed_today', 14);
        $weightRescued   = '248kg Rescued';
        $trendPct        = '+12%';
 
        $recentHistory = Session::get('pickup_history', [
            ['order_id' => 'ORD-8291', 'collector' => 'Green Valley Pantry',  'time' => '10:15 AM', 'weight_kg' => 45],
            ['order_id' => 'ORD-8288', 'collector' => 'Community Food Bank',  'time' => '09:42 AM', 'weight_kg' => 112],
            ['order_id' => 'ORD-8285', 'collector' => 'St. Jude Kitchen',     'time' => '09:15 AM', 'weight_kg' => 28],
            ['order_id' => 'ORD-8280', 'collector' => 'Homeless Outreach',    'time' => '08:50 AM', 'weight_kg' => 64],
            ['order_id' => 'ORD-8277', 'collector' => 'Jane Doe (Individual)','time' => '08:32 AM', 'weight_kg' => 5],
        ]);
 
        ob_start();
        require __DIR__ . '/../Views/employee/pickup_verification.php';
        $content = ob_get_clean();
 
        require __DIR__ . '/../Views/layouts/main.php';
    }
 
    public function verifyPickupToken(): void
    {
        $token = strtoupper(trim($_POST['token'] ?? ''));
        $tokens = $this->sampleTokens();
 
        if ($token === '') {
            Session::set('token_error', 'Enter a token to continue.');
        } elseif (!isset($tokens[$token])) {
            Session::set('token_error', 'That token was not recognized. Double-check and try again.');
            Session::set('token_old', $token);
        } else {
            Session::set('verified_pickup', $tokens[$token]);
        }
 
        header('Location: /employee/pickups/verify');
        exit;
    }
 
    public function resetPickupVerification(): void
    {
        Session::forget('verified_pickup');
        header('Location: /employee/pickups/verify');
        exit;
    }
 
    public function completePickup(): void
    {
        $pendingPickup = Session::get('verified_pickup');
        $orderId = $_POST['order_id'] ?? null;
 
        // Guard against a stale/tampered form post that doesn't match what
        // was actually verified.
        if (!$pendingPickup || $pendingPickup['order_id'] !== $orderId) {
            header('Location: /employee/pickups/verify');
            exit;
        }
 
        // TODO: replace with a real update once Pickup.php exists —
        // e.g. $this->pickupModel->markCompleted($orderId).
        $history = Session::get('pickup_history', []);
        array_unshift($history, [
            'order_id'  => $pendingPickup['order_id'],
            'collector' => $pendingPickup['collector'],
            'time'      => date('g:i A'),
            'weight_kg' => random_int(5, 120), // placeholder until real weights exist
        ]);
        Session::set('pickup_history', $history);
        Session::set('pickups_completed_today', Session::get('pickups_completed_today', 14) + 1);
 
        Session::forget('verified_pickup');
        Session::flash('success', 'Pickup for ' . $pendingPickup['collector'] . ' marked complete.');
        header('Location: /employee/pickups/verify');
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
        ];
 
        ob_start();
        require __DIR__ . '/../Views/employee/listing_published.php';
        $content = ob_get_clean();
 
        require __DIR__ . '/../Views/layouts/main.php';
    }
}