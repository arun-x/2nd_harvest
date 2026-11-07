<?php
/**
 * app/Controllers/AdminController.php
 * Admin & staff portal. The sign-in page is only reachable at /admin —
 * the public /login form has no admin role option and rejects admin
 * accounts, so this is the single entry point for administrators.
 *
 * Every other action goes through requireAdmin(), which only accepts a
 * session that signed in through this controller's /admin form (the
 * 'admin_authenticated' flag). Every POST also checks the CSRF token and
 * writes an audit_log row.
 */

require_once __DIR__ . '/../Views/admin/partials/helpers.php';

class AdminController extends BaseController
{
    // ---------------------------------------------------------------
    // Sign in
    // ---------------------------------------------------------------

    public function login(): void
    {
        // Already signed in through this form — skip it.
        if (Session::get('admin_authenticated') === true) {
            $this->redirect('/admin/dashboard');
        }

        $loginError = Session::get('admin_login_error');
        $oldEmail   = Session::get('admin_login_old_email', '');
        Session::forget('admin_login_error');
        Session::forget('admin_login_old_email');

        require __DIR__ . '/../Views/admin/login.php';
    }

    public function authenticate(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->failLogin('Enter your admin email and password.', $email);
        }

        $user = User::findByEmail($email);

        // One generic message for wrong password AND non-admin accounts, so
        // the form can't be used to probe which emails belong to admins.
        if (!$user
            || !password_verify($password, $user['password_hash'])
            || $user['role'] !== 'admin') {
            $this->failLogin('Invalid admin credentials.', $email);
        }

        if ($user['status'] === 'locked') {
            $this->failLogin('This admin account is locked. Contact a system administrator.', $email);
        }

        // New session id on privilege change — prevents session fixation.
        session_regenerate_id(true);

        Session::set('user_id', (int) $user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_email', $user['email']);
        Session::set('user', [
            'id'   => (int) $user['id'],
            'role' => $user['role'],
            'name' => $user['full_name'],
        ]);
        Session::set('admin_authenticated', true);

        AuditLog::record((int) $user['id'], 'admin.login', 'user', (int) $user['id']);
        $this->redirect('/admin/dashboard');
    }

    private function failLogin(string $message, string $email): void
    {
        Session::set('admin_login_error', $message);
        Session::set('admin_login_old_email', $email);
        $this->redirect('/admin');
    }

    // ---------------------------------------------------------------
    // Dashboard
    // ---------------------------------------------------------------

    public function dashboard(): void
    {
        $this->requireAdmin();

        $from = date('Y-m-01');
        $to   = date('Y-m-d');

        $counts    = Report::platformCounts();
        $month     = Report::summary($from, $to);
        $allTime   = Report::summary('2000-01-01', $to);
        $disputes  = Dispute::counts();
        $pending   = User::adminList(['status' => 'pending']);
        $openDisputes = array_merge(Dispute::all('open'), Dispute::all('pending_info'));

        // Moderation queue: everything waiting on an admin, oldest first.
        $queue = [];
        foreach ($pending as $u) {
            $queue[] = [
                'dot'   => 'orange',
                'title' => $u['outlet_name'] ?: ($u['org_name'] ?: $u['full_name']),
                'type'  => 'Registration',
                'desc'  => admin_role_label($u['role']) . ' awaiting verification',
                'when'  => $u['created_at'],
                'href'  => '/admin/registrations',
            ];
        }
        foreach ($openDisputes as $d) {
            $queue[] = [
                'dot'   => 'blue',
                'title' => $d['subject'],
                'type'  => 'Dispute',
                'desc'  => 'Raised by ' . $d['raised_by_name'],
                'when'  => $d['created_at'],
                'href'  => '/admin/disputes?id=' . $d['id'],
            ];
        }
        usort($queue, fn($a, $b) => strcmp($a['when'], $b['when']));
        $queueTotal = count($queue);
        $queue = array_slice($queue, 0, 5);

        $activity = AuditLog::search([], 8);

        $this->render('admin/dashboard', compact(
            'counts', 'month', 'allTime', 'disputes', 'queue', 'queueTotal', 'activity'
        ));
    }

    // ---------------------------------------------------------------
    // Registrations — verify & approve
    // ---------------------------------------------------------------

    public function registrations(): void
    {
        $this->requireAdmin();

        $statuses = ['pending', 'approved', 'rejected', 'locked'];
        $roles    = ['employee', 'charity', 'consumer'];

        $filters = [
            'status' => in_array($_GET['status'] ?? '', $statuses, true) ? $_GET['status'] : 'pending',
            'role'   => in_array($_GET['role'] ?? '', $roles, true) ? $_GET['role'] : '',
            'q'      => trim($_GET['q'] ?? ''),
        ];

        $rows = array_filter(
            User::adminList($filters),
            fn($u) => $u['role'] !== 'admin'
        );
        $counts = User::statusCounts($roles);

        $this->render('admin/registrations', compact('rows', 'counts', 'filters'));
    }

    public function approveRegistration(string $id): void
    {
        $admin = $this->requireAdmin();
        $this->verifyCsrf();

        $user = User::find((int) $id);
        if (!$user || $user['role'] === 'admin') {
            $this->back('/admin/registrations', 'error', 'Registration not found.');
        }

        User::setStatus((int) $id, 'approved');
        Notification::send((int) $id, 'Your 2nd Harvest account has been verified and approved. You can now sign in.', 'account_approved');
        AuditLog::record($admin->id, 'registration.approved', 'user', (int) $id);

        $this->back('/admin/registrations', 'success', $user['full_name'] . ' has been approved.');
    }

    public function rejectRegistration(string $id): void
    {
        $admin = $this->requireAdmin();
        $this->verifyCsrf();

        $reason = trim($_POST['reason'] ?? '');
        if ($reason === '') {
            $this->back('/admin/registrations', 'error', 'A reason is required to reject a registration.');
        }

        $user = User::find((int) $id);
        if (!$user || $user['role'] === 'admin') {
            $this->back('/admin/registrations', 'error', 'Registration not found.');
        }

        User::setStatus((int) $id, 'rejected');
        Notification::send((int) $id, 'Your 2nd Harvest registration was not approved: ' . $reason, 'account_rejected');
        AuditLog::record($admin->id, 'registration.rejected', 'user', (int) $id);

        $this->back('/admin/registrations', 'success', $user['full_name'] . ' has been rejected.');
    }

    // ---------------------------------------------------------------
    // Listings — platform-wide moderation
    // ---------------------------------------------------------------

    public function listings(): void
    {
        $this->requireAdmin();

        $filters = [
            'q'        => trim($_GET['q'] ?? ''),
            'status'   => in_array($_GET['status'] ?? '', ['available', 'reserved', 'collected', 'expired', 'removed'], true) ? $_GET['status'] : '',
            'category' => in_array($_GET['category'] ?? '', ['fruit', 'vegetable'], true) ? $_GET['category'] : '',
        ];

        $rows   = Listing::adminList($filters);
        $counts = Listing::statusCounts();

        $selected = null;
        $selectedReservations = [];
        if (!empty($_GET['id'])) {
            $selected = Listing::adminList(['id' => (int) $_GET['id']])[0] ?? null;
            if ($selected) {
                $selectedReservations = Reservation::forListing((int) $selected['id']);
            }
        }

        $this->render('admin/listings', compact('rows', 'counts', 'filters', 'selected', 'selectedReservations'));
    }

    public function removeListing(string $id): void
    {
        $admin = $this->requireAdmin();
        $this->verifyCsrf();

        $reason  = trim($_POST['reason'] ?? '');
        $listing = Listing::find((int) $id);
        if (!$listing) {
            $this->back('/admin/listings', 'error', 'Listing not found.');
        }
        if ($reason === '') {
            $this->back('/admin/listings?id=' . (int) $id, 'error', 'A reason is required to remove a listing.');
        }

        Listing::setStatus((int) $id, 'removed');
        Notification::send(
            (int) $listing['posted_by'],
            'Your listing "' . $listing['item_name'] . '" was removed by an administrator: ' . $reason,
            'listing_removed'
        );
        AuditLog::record($admin->id, 'listing.removed', 'listing', (int) $id);

        // Anyone holding a reservation on it can no longer collect — cancel
        // those and tell each person.
        $cancelled = Reservation::cancelActiveForListing((int) $id);
        foreach ($cancelled as $r) {
            Notification::send(
                (int) $r['user_id'],
                'Your reservation for "' . $listing['item_name'] . '" was cancelled because the listing was removed.',
                'reservation_cancelled'
            );
            AuditLog::record($admin->id, 'reservation.cancelled', 'reservation', (int) $r['id']);
        }

        $message = 'Listing removed and outlet notified.';
        if ($cancelled) {
            $message .= ' ' . count($cancelled) . ' active reservation(s) cancelled.';
        }
        $this->back('/admin/listings?id=' . (int) $id, 'success', $message);
    }

    public function restoreListing(string $id): void
    {
        $admin = $this->requireAdmin();
        $this->verifyCsrf();

        $listing = Listing::find((int) $id);
        if (!$listing || $listing['status'] !== 'removed') {
            $this->back('/admin/listings', 'error', 'Only removed listings can be restored.');
        }

        // Restored listings past their expiry go straight to expired.
        $status = $listing['expiry_date'] < date('Y-m-d') ? 'expired' : 'available';
        Listing::setStatus((int) $id, $status);
        AuditLog::record($admin->id, 'listing.restored', 'listing', (int) $id);

        $this->back('/admin/listings?id=' . (int) $id, 'success', 'Listing restored as ' . $status . '.');
    }

    // ---------------------------------------------------------------
    // Disputes
    // ---------------------------------------------------------------

    public function disputes(): void
    {
        $this->requireAdmin();

        $status = in_array($_GET['status'] ?? '', ['open', 'pending_info', 'resolved'], true) ? $_GET['status'] : 'open';
        $rows   = Dispute::all($status);
        $counts = Dispute::counts();

        $selected = null;
        if (!empty($_GET['id'])) {
            $selected = Dispute::find((int) $_GET['id']);
            if ($selected) {
                $status = $selected['status'];
                $rows   = Dispute::all($status);
            }
        } elseif ($rows) {
            $selected = $rows[0];
        }

        $this->render('admin/disputes', compact('rows', 'counts', 'status', 'selected'));
    }

    public function requestDisputeInfo(string $id): void
    {
        $admin = $this->requireAdmin();
        $this->verifyCsrf();

        $message = trim($_POST['message'] ?? '');
        $dispute = Dispute::find((int) $id);
        if (!$dispute || $message === '') {
            $this->back('/admin/disputes?id=' . (int) $id, 'error', 'Write a message to request more information.');
        }

        Dispute::markPendingInfo((int) $id);
        Notification::send(
            (int) $dispute['raised_by'],
            'Admin needs more info on your dispute "' . $dispute['subject'] . '": ' . $message,
            'dispute_info_requested'
        );
        AuditLog::record($admin->id, 'dispute.info_requested', 'dispute', (int) $id);

        $this->back('/admin/disputes?id=' . (int) $id, 'success', 'Information requested from ' . $dispute['raised_by_name'] . '.');
    }

    public function resolveDispute(string $id): void
    {
        $admin = $this->requireAdmin();
        $this->verifyCsrf();

        $resolution = trim($_POST['resolution'] ?? '');
        $dispute    = Dispute::find((int) $id);
        if (!$dispute || $resolution === '') {
            $this->back('/admin/disputes?id=' . (int) $id, 'error', 'A written resolution is required.');
        }

        if (!Dispute::resolve((int) $id, $resolution)) {
            $this->back('/admin/disputes?id=' . (int) $id, 'error', 'This dispute is already resolved.');
        }
        AuditLog::record($admin->id, 'dispute.resolved', 'dispute', (int) $id);

        $notice = 'Your dispute "' . $dispute['subject'] . '" was resolved: ' . $resolution;
        Notification::send((int) $dispute['raised_by'], $notice, 'dispute_resolved');

        if (!empty($dispute['against_user'])) {
            Notification::send((int) $dispute['against_user'], $notice, 'dispute_resolved');
        }

        $this->back('/admin/disputes?id=' . (int) $id, 'success', 'Dispute resolved.');
    }

    // ---------------------------------------------------------------
    // Reports & analytics
    // ---------------------------------------------------------------

    public function reports(): void
    {
        $this->requireAdmin();
        [$from, $to] = $this->reportRange();

        $summary = Report::summary($from, $to);
        $daily   = Report::dailyKg($from, $to);
        $outlets = Report::byOutlet($from, $to);

        $this->render('admin/reports', compact('from', 'to', 'summary', 'daily', 'outlets'));
    }

    public function exportReport(): void
    {
        $admin = $this->requireAdmin();
        [$from, $to] = $this->reportRange();

        $summary = Report::summary($from, $to);
        $outlets = Report::byOutlet($from, $to);
        $daily   = Report::dailyKg($from, $to);

        AuditLog::record($admin->id, 'report.exported', 'report', 0);

        $this->csvHeaders("2nd-harvest-report_{$from}_to_{$to}.csv");
        $out = fopen('php://output', 'w');

        fputcsv($out, ['2nd Harvest analytics report', "$from to $to"]);
        fputcsv($out, []);
        fputcsv($out, ['Summary']);
        foreach ([
            'Food rescued (kg)'         => $summary['kg_rescued'],
            'Charity priority (kg)'     => $summary['charity_kg'],
            'Consumer paid (kg)'        => $summary['consumer_kg'],
            'Meals equivalent'          => $summary['meals'],
            'Listings posted'           => $summary['listings_posted'],
            'Food listed (kg)'          => $summary['kg_listed'],
            'Food expired (kg)'         => $summary['kg_expired'],
            'Rescue rate (%)'           => $summary['rescue_rate'] ?? 'n/a',
            'Reservations'              => $summary['reservations'],
            'Completed pickups'         => $summary['completed'],
            'Cancelled'                 => $summary['cancelled'],
            'Consumer revenue (LKR)'    => $summary['revenue'],
        ] as $label => $value) {
            fputcsv($out, [$label, $value]);
        }

        fputcsv($out, []);
        fputcsv($out, ['By outlet']);
        fputcsv($out, ['Outlet', 'Region', 'Listings posted', 'Rescued (kg)', 'Expired (kg)', 'Reservations', 'Revenue (LKR)']);
        foreach ($outlets as $o) {
            fputcsv($out, [$o['outlet_name'], $o['region'], $o['listings_posted'], $o['kg_rescued'], $o['kg_expired'], $o['reservations'], $o['revenue']]);
        }

        fputcsv($out, []);
        fputcsv($out, ['Daily food rescued']);
        fputcsv($out, ['Date', 'Rescued (kg)']);
        foreach ($daily as $day => $kg) {
            fputcsv($out, [$day, $kg]);
        }

        fclose($out);
        exit;
    }

    /** @return array{0:string,1:string} validated from/to, default last 30 days */
    private function reportRange(): array
    {
        $valid = fn($d) => is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d);
        $to    = $valid($_GET['to'] ?? null) ? $_GET['to'] : date('Y-m-d');
        $from  = $valid($_GET['from'] ?? null) ? $_GET['from'] : date('Y-m-d', strtotime($to . ' -29 days'));
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }
        // Cap at one year so the daily series stays readable.
        if (strtotime($to) - strtotime($from) > 366 * 86400) {
            $from = date('Y-m-d', strtotime($to . ' -365 days'));
        }
        return [$from, $to];
    }

    // ---------------------------------------------------------------
    // User management
    // ---------------------------------------------------------------

    public function users(): void
    {
        $this->requireAdmin();

        $filters = [
            'role'   => in_array($_GET['role'] ?? '', ['employee', 'charity', 'consumer', 'admin'], true) ? $_GET['role'] : '',
            'status' => in_array($_GET['status'] ?? '', ['pending', 'approved', 'rejected', 'locked'], true) ? $_GET['status'] : '',
            'q'      => trim($_GET['q'] ?? ''),
        ];
        $rows   = User::adminList($filters);
        $counts = User::statusCounts();

        $this->render('admin/users', compact('rows', 'filters', 'counts'));
    }

    public function lockUser(string $id): void
    {
        $admin = $this->requireAdmin();
        $this->verifyCsrf();

        if ((int) $id === $admin->id) {
            $this->back('/admin/users', 'error', "You can't lock your own account.");
        }
        $user = User::find((int) $id);
        if (!$user) {
            $this->back('/admin/users', 'error', 'User not found.');
        }

        User::setStatus((int) $id, 'locked');
        Notification::send((int) $id, 'Your 2nd Harvest account has been locked by an administrator.', 'account_locked');
        AuditLog::record($admin->id, 'user.locked', 'user', (int) $id);

        $this->back('/admin/users', 'success', $user['full_name'] . ' has been locked.');
    }

    public function unlockUser(string $id): void
    {
        $admin = $this->requireAdmin();
        $this->verifyCsrf();

        $user = User::find((int) $id);
        if (!$user || $user['status'] !== 'locked') {
            $this->back('/admin/users', 'error', 'Only locked accounts can be unlocked.');
        }

        User::setStatus((int) $id, 'approved');
        Notification::send((int) $id, 'Your 2nd Harvest account has been unlocked.', 'account_unlocked');
        AuditLog::record($admin->id, 'user.unlocked', 'user', (int) $id);

        $this->back('/admin/users', 'success', $user['full_name'] . ' has been unlocked.');
    }

    // ---------------------------------------------------------------
    // Audit log
    // ---------------------------------------------------------------

    public function auditLog(): void
    {
        $this->requireAdmin();

        $filters = [
            'q'      => trim($_GET['q'] ?? ''),
            'entity' => trim($_GET['entity'] ?? ''),
        ];
        $perPage = 50;
        $total   = AuditLog::count($filters);
        $pages   = max(1, (int) ceil($total / $perPage));
        $page    = min($pages, max(1, (int) ($_GET['page'] ?? 1)));
        $rows    = AuditLog::search($filters, $perPage, ($page - 1) * $perPage);
        $entities = AuditLog::entities();

        $this->render('admin/audit_log', compact('rows', 'filters', 'total', 'page', 'pages', 'entities'));
    }

    public function exportAuditLog(): void
    {
        $this->requireAdmin();

        $filters = [
            'q'      => trim($_GET['q'] ?? ''),
            'entity' => trim($_GET['entity'] ?? ''),
        ];
        $rows = AuditLog::search($filters, 10000);

        $this->csvHeaders('2nd-harvest-audit-log_' . date('Y-m-d') . '.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['ID', 'Timestamp', 'User', 'Email', 'Role', 'Action', 'Entity', 'Entity ID']);
        foreach ($rows as $r) {
            fputcsv($out, [$r['id'], $r['created_at'], $r['full_name'], $r['email'], $r['role'], $r['action'], $r['entity'], $r['entity_id']]);
        }
        fclose($out);
        exit;
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Redirects to the /admin sign-in unless this session signed in through
     * it and the account is still an unlocked admin.
     */
    private function requireAdmin(): object
    {
        $user = Auth::user();
        if (Session::get('admin_authenticated') !== true || !$user || $user->role !== 'admin') {
            $this->redirect('/admin');
        }
        $row = User::find($user->id);
        if (!$row || $row['status'] === 'locked') {
            Session::forget('user_id');
            Session::forget('user_role');
            Session::forget('user');
            Session::forget('admin_authenticated');
            $this->redirect('/admin');
        }
        return $user;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $csrfToken   = $this->csrfToken();
        $adminBadges = [
            'admin.registrations' => User::statusCounts(['employee', 'charity', 'consumer'])['pending'],
            'admin.disputes'      => array_sum(array_intersect_key(Dispute::counts(), ['open' => 1, 'pending_info' => 1])),
        ];
        $searchAction      = BASE_URL . '/admin/listings';
        $searchPlaceholder = 'Search listings...';
        $extraStylesheets  = ['dashboard.css', 'admin.css'];

        ob_start();
        require __DIR__ . '/../Views/' . $view . '.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/main.php';
    }

    private function csrfToken(): string
    {
        $token = Session::get('admin_csrf');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('admin_csrf', $token);
        }
        return $token;
    }

    private function verifyCsrf(): void
    {
        $sent = $_POST['_csrf'] ?? '';
        if (!is_string($sent) || !hash_equals($this->csrfToken(), $sent)) {
            $this->back('/admin/dashboard', 'error', 'Your session expired. Please try again.');
        }
    }

    private function csvHeaders(string $filename): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM so Excel reads Sinhala/Tamil names correctly
    }

    private function back(string $path, string $type, string $message): void
    {
        Session::flash($type, $message);
        $this->redirect($path);
    }

    private function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}
