<?php
/**
 * app/Controllers/AuthController.php
 * Handles the public auth flow: role selection -> role-specific
 * registration form -> save, and login/logout.
 *
 * NOTE: There's no Models/User.php yet, so registration doesn't persist
 * anywhere and login doesn't check real credentials — see the TODOs below
 * for exactly where to wire in the real database calls once that model
 * exists.
 */
 
class AuthController extends BaseController
{
    private function panelContent(): array
    {
        return [
            'supermarket' => [
                'heading' => 'Partner with us to reduce food waste.',
                'desc' => 'Turn your store surplus into local community impact. Effortlessly list, schedule, and verify food rescue donations while tracking your ESG metrics.',
                'benefits' => [
                    ['title' => 'Simple Logistics', 'desc' => 'Instantly upload batch surplus details directly from your inventory.'],
                    ['title' => 'Verified Tax Benefits', 'desc' => 'Generate audit-ready certificates for food donation write-offs.'],
                ],
                'image' => BASE_URL . '/assets/images/register-supermarket.jpg',
            ],
            'charity' => [
                'heading' => 'Make a difference. Register your Charity.',
                'desc' => 'Connect directly with local supermarkets to secure quality surplus food. Power your meal programs and support families in need while effortlessly tracking your rescue impact.',
                'benefits' => [
                    ['title' => 'Easy Donation Tracking', 'desc' => 'Coordinate food pick-ups and log donation sizes directly within your portal.'],
                    ['title' => 'Community Impact Reports', 'desc' => 'Generate live, shareable social and environmental metrics for your donors.'],
                ],
                'image' => BASE_URL . '/assets/images/register-charity.jpg',
            ],
            'consumer' => [
                'heading' => 'Join the movement to end food waste.',
                'desc' => "Whether you're a partner outlet or a consumer looking for fresh surplus, 2nd Harvest connects you to what matters.",
                'benefits' => [
                    ['title' => 'Eco-Friendly', 'desc' => 'Reduce your carbon footprint with every basket rescued.'],
                    ['title' => 'Community Driven', 'desc' => 'Support local businesses and help neighbors in need.'],
                ],
                'image' => BASE_URL . '/assets/images/register-consumer.jpg',
            ],
        ];
    }
 
    public function registerRoleSelect(): void
    {
        // $role intentionally left unset — register.php treats that as
        // "show the role-select grid" rather than a specific form.
        require __DIR__ . '/../Views/auth/register.php';
    }
 
    private function renderRegisterForm(string $role): void
    {
        $panel  = $this->panelContent()[$role];
        $old    = Session::get('register_old', []);
        $errors = Session::get('register_errors', []);
        Session::forget('register_old');
        Session::forget('register_errors');
 
        require __DIR__ . '/../Views/auth/register.php';
    }
 
    public function registerSupermarket(): void { $this->renderRegisterForm('supermarket'); }
    public function registerCharity(): void { $this->renderRegisterForm('charity'); }
    public function registerConsumer(): void { $this->renderRegisterForm('consumer'); }
 
    public function store(): void
    {
        $role = $_POST['role'] ?? '';
 
        if (!in_array($role, ['supermarket', 'charity', 'consumer'], true)) {
            header('Location: ' . BASE_URL . '/register');
            exit;
        }
 
        $errors = $this->validateRegistration($role, $_POST);
 
        if (!empty($errors)) {
            Session::set('register_old', $_POST);
            Session::set('register_errors', $errors);
            header('Location: ' . BASE_URL . '/register/' . $role);
            exit;
        }
 
        if (User::findByEmail($_POST['email']) !== null) {
            Session::set('register_old', $_POST);
            Session::set('register_errors', ['email' => 'That email is already registered.']);
            header('Location: ' . BASE_URL . '/register/' . $role);
            exit;
        }

        $dbRole = ['supermarket' => 'employee', 'charity' => 'charity', 'consumer' => 'consumer'][$role];

        $fullName = $role === 'consumer'
            ? trim($_POST['full_name'])
            : trim($_POST['contact_person']);

        $pdo = Database::connection();
        try {
            $pdo->beginTransaction();

            $userId = User::create([
                'role'          => $dbRole,
                'email'         => trim($_POST['email']),
                'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'full_name'     => $fullName,
                'phone'         => $_POST['phone'] ?? null,
                // Supermarkets and charities are verified by an admin
                // (Admin > Registrations) before they can sign in.
                'status'        => $role === 'consumer' ? 'approved' : 'pending',
            ]);

            if ($role === 'supermarket') {
                Outlet::create([
                    'user_id'         => $userId,
                    'outlet_name'     => trim($_POST['organization_name']),
                    'branch_location' => trim($_POST['address']),
                    'region'          => trim($_POST['branch_name']),
                    'business_reg_number' => trim($_POST['business_reg_number']),
                ]);
            } elseif ($role === 'charity') {
                Charity::create([
                    'user_id'           => $userId,
                    'org_name'          => trim($_POST['charity_name']),
                    'charity_reg_number' => trim($_POST['charity_reg_number']),
                    'address'           => trim($_POST['service_area']),
                    'operational_focus' => trim($_POST['charity_type']),
                ]);
            }

            [$recoveryCode, $recoveryHash] = Recovery::newRecoveryCode();
            User::updateRecoveryCode($userId, $recoveryHash);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            Session::set('register_old', $_POST);
            Session::set('register_errors', ['email' => 'Could not create account. Please try again.']);
            header('Location: ' . BASE_URL . '/register/' . $role);
            exit;
        }

        Session::flash('success', $role === 'consumer'
            ? 'Account created! You can now log in.'
            : 'Account created! An administrator will verify your registration before you can log in.');
        $this->showRecoveryCode($recoveryCode, 'register', '/login');
    }
 
    private function validateRegistration(string $role, array $input): array
    {
        $errors = [];
 
        $requiredByRole = [
            'supermarket' => ['organization_name', 'branch_name', 'business_reg_number', 'contact_person', 'email', 'phone', 'password', 'address'],
            'charity'     => ['charity_reg_number', 'charity_name', 'charity_type', 'contact_person', 'email', 'phone', 'password', 'service_area'],
            'consumer'    => ['full_name', 'email', 'password', 'location'],
        ];
 
        foreach ($requiredByRole[$role] as $field) {
            if (trim($input[$field] ?? '') === '') {
                $errors[$field] = 'This field is required.';
            }
        }
 
        if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
 
        if (!empty($input['password']) && strlen($input['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
 
        if (empty($input['agree'])) {
            $errors['agree'] = 'You must agree to the Terms of Service and Privacy Policy.';
        }
 
        return $errors;
    }
 
    public function login(): void
    {
        $loginError = Session::get('login_error');
        $oldEmail   = Session::get('login_old_email', '');
        $oldRole    = Session::get('login_old_role', 'supermarket');
        Session::forget('login_error');
        Session::forget('login_old_email');
        Session::forget('login_old_role');
        $flashes = Session::getFlash();

        require __DIR__ . '/../Views/auth/login.php';
    }

    public function terms(): void
    {
        require __DIR__ . '/../Views/auth/terms.php';
    }

    public function privacy(): void
    {
        require __DIR__ . '/../Views/auth/privacy.php';
    }

    public function cookies(): void
    {
        require __DIR__ . '/../Views/auth/cookies.php';
    }
 
    public function authenticate(): void
    {
        $role     = $_POST['role'] ?? 'supermarket';
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Map the UI role value to the DB role value ('supermarket' -> 'employee').
        $uiToDbRole = [
            'supermarket' => 'employee',
            'charity'     => 'charity',
            'consumer'    => 'consumer',
        ];
        if (!isset($uiToDbRole[$role])) {
            Session::set('login_error', 'Please choose a valid role.');
            Session::set('login_old_email', $email);
            Session::set('login_old_role', 'supermarket');
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $expectedDbRole = $uiToDbRole[$role];

        if ($email === '' || $password === '') {
            Session::set('login_error', 'Enter your email and password.');
            Session::set('login_old_email', $email);
            Session::set('login_old_role', $role);
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            Session::set('login_error', 'Incorrect email or password.');
            Session::set('login_old_email', $email);
            Session::set('login_old_role', $role);
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Enforce that the account's role matches the selected role on the form.
        // Without this, a Consumer could sign in from the Supermarket tab and vice versa.
        if ($user['role'] !== $expectedDbRole) {
            $roleLabels = [
                'supermarket' => 'Super Market',
                'charity'     => 'Charity',
                'consumer'    => 'Consumer',
            ];
            Session::set(
                'login_error',
                'Those credentials don\'t match a ' . $roleLabels[$role] . ' account.'
            );
            Session::set('login_old_email', $email);
            Session::set('login_old_role', $role);
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Only admin-approved accounts may sign in.
        $statusErrors = [
            'pending'  => 'Your registration is still being verified by an administrator.',
            'rejected' => 'Your registration was not approved. Please contact support.',
            'locked'   => 'Your account has been locked. Please contact support.',
        ];
        if (isset($statusErrors[$user['status']])) {
            Session::set('login_error', $statusErrors[$user['status']]);
            Session::set('login_old_email', $email);
            Session::set('login_old_role', $role);
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        Session::set('user_id', (int) $user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_email', $user['email']);
        // The admin panel only opens for sessions signed in via /admin.
        Session::forget('admin_authenticated');

        // Also populate the single 'user' array that Arun's Consumer module
        // reads via App\Core\Session::get('user'). Both modules now see the
        // same logged-in user from the same $_SESSION.
        Session::set('user', [
            'id'   => (int) $user['id'],
            'role' => $user['role'],
            'name' => $user['full_name'],
        ]);

        $destinations = [
            'employee' => BASE_URL . '/employee/dashboard',
            'charity'  => BASE_URL . '/', // TODO: point at the charity dashboard once it's built
            'consumer' => BASE_URL . '/consumer/dashboard',
            'admin'    => BASE_URL . '/admin', // admins must sign in via the /admin form
        ];
 
        header('Location: ' . ($destinations[$user['role']] ?? BASE_URL . '/'));
        exit;
    }

    public function logout(): void
    {
        $wasAdmin = Session::get('user_role') === 'admin';
        Session::forget('user_id');
        Session::forget('user_role');
        Session::forget('user_email');
        Session::forget('user');
        Session::forget('admin_csrf');
        Session::forget('admin_authenticated');
        header('Location: ' . BASE_URL . ($wasAdmin ? '/admin' : '/'));
        exit;
    }

    // ---------------------------------------------------------------
    // Forgot password (no email involved). Two ways to prove who you are:
    //   1) the recovery code handed out at registration, or
    //   2) a request an admin verifies and approves (Admin > Password
    //      Resets); the user comes back with their email + request number.
    // Either way, success puts a short-lived 'password_reset' grant in
    // this session and sends the user to /reset-password.
    // ---------------------------------------------------------------

    public const APPROVAL_VALID_MINUTES = 60;   // how long an admin approval stays usable
    private const RESET_WINDOW_SECONDS  = 900;  // time to finish the new-password form
    private const MAX_FAILED_CHECKS     = 5;
    private const LOCKOUT_SECONDS       = 900;
    private const MAX_OPEN_REQUESTS     = 3;

    public function forgotPassword(): void
    {
        $mode = in_array($_GET['mode'] ?? '', ['code', 'request', 'status'], true) ? $_GET['mode'] : 'code';

        $error    = Session::get('forgot_error');
        $notice   = Session::get('forgot_notice');
        $oldEmail = Session::get('forgot_old_email', '');
        $ticket   = Session::get('forgot_ticket'); // request number, shown once
        foreach (['forgot_error', 'forgot_notice', 'forgot_old_email', 'forgot_ticket'] as $key) {
            Session::forget($key);
        }
        $csrfToken = $this->authCsrfToken();

        require __DIR__ . '/../Views/auth/forgot_password.php';
    }

    public function verifyRecoveryCode(): void
    {
        $this->verifyAuthCsrf('/forgot-password?mode=code');
        $this->guardAttempts('code');

        $email = trim($_POST['email'] ?? '');
        $code  = trim($_POST['recovery_code'] ?? '');
        if ($email === '' || $code === '') {
            $this->forgotFail('code', 'Enter your email and recovery code.', $email, false);
        }

        $user = User::findByEmail($email);
        if (!$user || !Recovery::verifyRecoveryCode($code, $user['recovery_code_hash'] ?? null)) {
            $this->forgotFail('code', "That email and recovery code don't match.", $email);
        }

        $this->grantReset($user, null, 'code');
    }

    public function submitResetRequest(): void
    {
        $this->verifyAuthCsrf('/forgot-password?mode=request');

        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->forgotFail('request', 'Enter a valid email address.', $email, false);
        }

        [$number, $hash] = Recovery::newRequestNumber();

        // Unknown emails get a request number too, so this form can't be used
        // to find out who has an account. The cap stops anyone flooding the
        // admin queue for one account.
        $user = User::findByEmail($email);
        if ($user && PasswordResetRequest::countOpenFor((int) $user['id']) < self::MAX_OPEN_REQUESTS) {
            PasswordResetRequest::create((int) $user['id'], $hash, substr($number, -4));
        }

        Session::set('forgot_ticket', $number);
        Session::set('forgot_old_email', $email);
        $this->redirect('/forgot-password?mode=request');
    }

    public function checkResetRequest(): void
    {
        $this->verifyAuthCsrf('/forgot-password?mode=status');
        $this->guardAttempts('status');

        $email  = trim($_POST['email'] ?? '');
        $number = trim($_POST['request_number'] ?? '');
        if ($email === '' || $number === '') {
            $this->forgotFail('status', 'Enter your email and request number.', $email, false);
        }

        $user    = User::findByEmail($email);
        $request = $user ? PasswordResetRequest::findForUser((int) $user['id'], Recovery::requestHash($number)) : null;
        if (!$request) {
            $this->forgotFail('status', "We couldn't find a request with that email and request number.", $email);
        }

        if ($request['status'] === 'approved' && strtotime($request['approved_until']) < time()) {
            PasswordResetRequest::setStatus((int) $request['id'], 'expired');
            $request['status'] = 'expired';
        }

        switch ($request['status']) {
            case 'approved':
                $this->grantReset($user, (int) $request['id'], 'status');
                // grantReset() redirects
            case 'pending':
                Session::set('forgot_notice', 'Your request is still waiting for an administrator. They may contact you to confirm your identity. Please check back later.');
                Session::set('forgot_old_email', $email);
                $this->redirect('/forgot-password?mode=status');
            case 'rejected':
                $reason = $request['reject_reason'] ? ' Reason: ' . $request['reject_reason'] : '';
                $this->forgotFail('status', 'Your request was not approved.' . $reason, $email, false);
            case 'completed':
                $this->forgotFail('status', 'This request has already been used to reset your password.', $email, false);
            case 'expired':
                $this->forgotFail('status', 'The approval for this request has expired. Please submit a new request.', $email, false);
            default: // cancelled
                $this->forgotFail('status', 'This request is no longer active. Please submit a new request.', $email, false);
        }
    }

    public function resetPassword(): void
    {
        $grant  = $this->resetGrant();
        $user   = User::find($grant['user_id']);
        $errors = Session::get('reset_errors', []);
        $error  = Session::get('forgot_error');
        Session::forget('reset_errors');
        Session::forget('forgot_error');
        $minutesLeft = max(1, (int) ceil(($grant['expires'] - time()) / 60));
        $csrfToken   = $this->authCsrfToken();

        require __DIR__ . '/../Views/auth/reset_password.php';
    }

    public function updateForgottenPassword(): void
    {
        $this->verifyAuthCsrf('/reset-password');
        $grant = $this->resetGrant();

        $next    = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');
        $errors  = [];
        if (strlen($next) < 8) {
            $errors['new_password'] = 'Password must be at least 8 characters.';
        }
        if ($next !== $confirm) {
            $errors['confirm_password'] = 'The passwords do not match.';
        }
        if ($errors) {
            Session::set('reset_errors', $errors);
            $this->redirect('/reset-password');
        }

        $userId = $grant['user_id'];
        $user   = User::find($userId);

        User::updatePassword($userId, password_hash($next, PASSWORD_DEFAULT));
        if ($grant['request_id']) {
            PasswordResetRequest::setStatus($grant['request_id'], 'completed');
        }
        PasswordResetRequest::cancelOpenFor($userId);

        // The old recovery code may have just been used, so always issue a fresh one.
        [$code, $hash] = Recovery::newRecoveryCode();
        User::updateRecoveryCode($userId, $hash);

        Notification::send($userId, 'Your 2nd Harvest password was reset. If this wasn\'t you, contact support immediately.', 'password_reset');
        AuditLog::record(
            $userId,
            $grant['request_id'] ? 'password.reset_admin_approved' : 'password.reset_recovery_code',
            'user',
            $userId
        );

        Session::forget('password_reset');
        Session::flash('success', 'Your password has been changed. You can now log in.');
        $this->showRecoveryCode($code, 'reset', $user && $user['role'] === 'admin' ? '/admin' : '/login');
    }

    /** One-time display of a freshly issued recovery code. */
    public function recoveryCode(): void
    {
        $display = Session::get('recovery_code_display');
        if (!$display) {
            $this->redirect('/login');
        }
        Session::forget('recovery_code_display');

        require __DIR__ . '/../Views/auth/recovery_code.php';
    }

    /** Signed-in users (e.g. accounts created before recovery codes existed) can issue a new code. */
    public function regenerateRecoveryCode(): void
    {
        $userId = (int) Session::get('user_id', 0);
        $user   = $userId ? User::find($userId) : null;
        if (!$user) {
            $this->redirect('/login');
        }

        $back = [
            'employee' => '/employee/profile#recovery',
            'consumer' => '/consumer/profile#recovery',
        ][$user['role']] ?? '/';

        if (!password_verify((string) ($_POST['current_password'] ?? ''), $user['password_hash'])) {
            $this->flashFor($user['role'], 'error', 'Current password is incorrect. Your recovery code was not changed.');
            $this->redirect($back);
        }

        [$code, $hash] = Recovery::newRecoveryCode();
        User::updateRecoveryCode($userId, $hash);
        AuditLog::record($userId, 'recovery_code.regenerated', 'user', $userId);

        $this->showRecoveryCode($code, 'regenerate', $back);
    }

    // ---------------------------------------------------------------
    // Forgot-password helpers
    // ---------------------------------------------------------------

    private function grantReset(array $user, ?int $requestId, string $mode): void
    {
        if ($user['status'] === 'locked') {
            $this->forgotFail($mode, 'This account has been locked. Please contact support.', $user['email'], false);
        }

        Session::forget('forgot_attempts');
        session_regenerate_id(true);
        Session::set('password_reset', [
            'user_id'    => (int) $user['id'],
            'request_id' => $requestId,
            'expires'    => time() + self::RESET_WINDOW_SECONDS,
        ]);
        $this->redirect('/reset-password');
    }

    /** @return array{user_id:int,request_id:?int,expires:int} */
    private function resetGrant(): array
    {
        $grant = Session::get('password_reset');
        if (!$grant || $grant['expires'] < time()) {
            Session::forget('password_reset');
            Session::set('forgot_error', 'Your reset session has expired. Please verify your identity again.');
            $this->redirect('/forgot-password');
        }
        return $grant;
    }

    /**
     * Failed code / request-number checks are counted per session; after
     * MAX_FAILED_CHECKS the forms refuse further checks for a while.
     */
    private function guardAttempts(string $mode): void
    {
        $attempts = Session::get('forgot_attempts');
        if (!$attempts) {
            return;
        }
        $wait = $attempts['since'] + self::LOCKOUT_SECONDS - time();
        if ($wait <= 0) {
            Session::forget('forgot_attempts');
            return;
        }
        if ($attempts['count'] >= self::MAX_FAILED_CHECKS) {
            $this->forgotFail($mode, 'Too many attempts. Please try again in ' . (int) ceil($wait / 60) . ' minutes.', trim($_POST['email'] ?? ''), false);
        }
    }

    private function forgotFail(string $mode, string $message, string $email, bool $countAttempt = true): void
    {
        if ($countAttempt) {
            $attempts = Session::get('forgot_attempts', ['count' => 0, 'since' => time()]);
            $attempts['count']++;
            Session::set('forgot_attempts', $attempts);
        }
        Session::set('forgot_error', $message);
        Session::set('forgot_old_email', $email);
        $this->redirect('/forgot-password?mode=' . $mode);
    }

    private function showRecoveryCode(string $code, string $context, string $next): void
    {
        Session::set('recovery_code_display', [
            'code'    => $code,
            'context' => $context, // 'register' | 'reset' | 'regenerate'
            'next'    => BASE_URL . $next,
        ]);
        $this->redirect('/recovery-code');
    }

    /** The Consumer module reads a different flash shape than the rest of the app. */
    private function flashFor(string $role, string $type, string $message): void
    {
        if ($role === 'consumer') {
            Session::set('flash', ['type' => $type, 'message' => $message]);
        } else {
            Session::flash($type, $message);
        }
    }

    private function authCsrfToken(): string
    {
        $token = Session::get('auth_csrf');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('auth_csrf', $token);
        }
        return $token;
    }

    private function verifyAuthCsrf(string $back): void
    {
        $sent = $_POST['_csrf'] ?? '';
        if (!is_string($sent) || !hash_equals($this->authCsrfToken(), $sent)) {
            Session::set('forgot_error', 'Your session expired. Please try again.');
            $this->redirect($back);
        }
    }

    private function redirect(string $path): void
    {
        header('Location: ' . BASE_URL . $path);
        exit;
    }
}
