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

        $inventory = [
            [
                'id' => 1, 'image' => 'https://placehold.co/80x80/e8f3ec/2f6b45?text=Veg',
                'name' => 'Organic Vegetable Mix', 'category' => 'Produce',
                'sku' => 'PRD-VG-012', 'quantity' => 12, 'expires' => '5/20/2024',
                'status' => 'active',
            ],
            [
                'id' => 2, 'image' => 'https://placehold.co/80x80/f3ead9/8a5a2b?text=Bread',
                'name' => 'Artisan Sourdough Loaf', 'category' => 'Bakery',
                'sku' => 'BKY-SD-005', 'quantity' => 8, 'expires' => '5/18/2024',
                'status' => 'expiring',
            ],
            [
                'id' => 3, 'image' => 'https://placehold.co/80x80/eaf1fb/2b5a8a?text=Milk',
                'name' => 'Farm Fresh Whole Milk', 'category' => 'Dairy',
                'sku' => 'DRY-MK-088', 'quantity' => 24, 'expires' => '5/19/2024',
                'status' => 'active',
            ],
            [
                'id' => 4, 'image' => 'https://placehold.co/80x80/fbeaf3/8a2b5a?text=Yogurt',
                'name' => 'Greek Yogurt Cups', 'category' => 'Dairy',
                'sku' => 'DRY-YG-102', 'quantity' => 15, 'expires' => '5/17/2024',
                'status' => 'reserved',
            ],
            [
                'id' => 5, 'image' => 'https://placehold.co/80x80/f3ead9/8a5a2b?text=Bread',
                'name' => 'Multigrain Baguettes', 'category' => 'Bakery',
                'sku' => 'BKY-BG-011', 'quantity' => 5, 'expires' => '5/15/2024',
                'status' => 'expired',
            ],
        ];

        $communityImpact = [
            'food_saved'        => '4,250 kg',
            'charities_served'  => 12,
        ];

        $alerts = [
            [
                'type' => 'danger',
                'title' => 'Expired: French Baguettes',
                'meta' => 'SKU BKY-BG-011 · 5 units',
                'action_label' => 'Clear Listing',
                'action_href' => '/employee/listings/5',
            ],
            [
                'type' => 'warning',
                'title' => 'Expiring Today: Sourdough',
                'meta' => '8 units remaining · Donate now',
                'action_label' => 'Push to Charity',
                'action_href' => '/employee/listings/2/push',
            ],
        ];

        $highlight = [
            'id' => 6,
            'image' => 'https://placehold.co/400x300/e8f3ec/2f6b45?text=Produce+Crate',
            'badge' => 'Available',
            'title' => 'Fresh Organic Produce Crate',
            'category' => 'Produce',
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

    public function createListing(): void
    {
        // GET: show the blank/prefilled form.
        // POST: validate + save, then either re-render with $errors/$old,
        // or redirect to the dashboard on success. Wired here as GET-only
        // for now so you can see the form render.

        $categories = [
            'produce' => 'Produce (Fresh Veg/Fruit)',
            'bakery'  => 'Bakery',
            'dairy'   => 'Dairy',
            'pantry'  => 'Pantry',
            'meals'   => 'Prepared Meals',
        ];

        $pickupWindows = [
            'immediate' => 'Immediate (Within 1 hr)',
            'today'     => 'Later Today',
            'tomorrow'  => 'Tomorrow',
        ];

        $draftCount = 2;
        $old = [];
        $errors = [];

        ob_start();
        require __DIR__ . '/../Views/employee/create_listing.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    public function pickupVerification(): void
    {
        $pendingPickup = [
            'order_id'  => 'ORD-8292',
            'collector' => 'Green Valley Pantry',
        ];

        $successfulToday = 14;
        $weightRescued   = '248kg Rescued';
        $trendPct        = '+12%';

        $recentHistory = [
            ['order_id' => 'ORD-8291', 'collector' => 'Green Valley Pantry',  'time' => '10:15 AM', 'weight_kg' => 45],
            ['order_id' => 'ORD-8288', 'collector' => 'Community Food Bank',  'time' => '09:42 AM', 'weight_kg' => 112],
            ['order_id' => 'ORD-8285', 'collector' => 'St. Jude Kitchen',     'time' => '09:15 AM', 'weight_kg' => 28],
            ['order_id' => 'ORD-8280', 'collector' => 'Homeless Outreach',    'time' => '08:50 AM', 'weight_kg' => 64],
            ['order_id' => 'ORD-8277', 'collector' => 'Jane Doe (Individual)','time' => '08:32 AM', 'weight_kg' => 5],
        ];

        ob_start();
        require __DIR__ . '/../Views/employee/pickup_verification.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }
}
