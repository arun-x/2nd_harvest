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
    // Business registration document upload (charity sign-up)
    private const DOC_MAX_BYTES = 5 * 1024 * 1024; // 5 MB
    private const DOC_ALLOWED   = [                 // detected MIME => stored extension
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
    ];

    /** Uploaded docs live OUTSIDE public/ so they can't be fetched by URL. */
    private function docStorageDir(): string
    {
        return __DIR__ . '/../../storage/charity_docs';
    }

    private function validateBusinessDoc(?array $file): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return 'Please upload your business registration document.';
        }
        if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            return 'That file is too large. The maximum size is 5 MB.';
        }
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            return 'The upload failed. Please try again.';
        }
        if ($file['size'] > self::DOC_MAX_BYTES) {
            return 'That file is too large. The maximum size is 5 MB.';
        }

        // Check the real content type, not the browser-supplied one or the file name.
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!isset(self::DOC_ALLOWED[$mime])) {
            return 'Only PDF, JPG or PNG files are allowed.';
        }
        return null;
    }

    /** Moves the validated upload into storage under a random name; returns the stored relative path. */
    private function saveBusinessDoc(array $file): string
    {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $dir  = $this->docStorageDir();

        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new RuntimeException('Could not create document storage directory.');
        }

        $name = bin2hex(random_bytes(16)) . '.' . self::DOC_ALLOWED[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            throw new RuntimeException('Could not store uploaded document.');
        }
        return 'charity_docs/' . $name;
    }

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

        $savedDocPath = null;
        $pdo = Database::connection();
        try {
            $pdo->beginTransaction();

            $userId = User::create([
                'role'          => $dbRole,
                'email'         => trim($_POST['email']),
                'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'full_name'     => $fullName,
                'phone'         => $_POST['phone'] ?? null,
                'status'        => 'approved',
            ]);

            if ($role === 'supermarket') {
                Outlet::create([
                    'user_id'         => $userId,
                    'outlet_name'     => trim($_POST['organization_name']),
                    'branch_location' => trim($_POST['address']),
                    'region'          => trim($_POST['branch_name']),
                ]);
            } elseif ($role === 'charity') {
                $savedDocPath = $this->saveBusinessDoc($_FILES['business_reg_doc']);

                Charity::create([
                    'user_id'               => $userId,
                    'org_name'              => trim($_POST['charity_name']),
                    'address'               => trim($_POST['service_area']),
                    'operational_focus'     => trim($_POST['charity_type']),
                    'verification_doc_path' => $savedDocPath,
                ]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            if ($savedDocPath !== null) {
                @unlink($this->docStorageDir() . '/' . basename($savedDocPath)); // don't leave an orphaned upload
            }
            Session::set('register_old', $_POST);
            Session::set('register_errors', ['email' => 'Could not create account. Please try again.']);
            header('Location: ' . BASE_URL . '/register/' . $role);
            exit;
        }

        Session::flash('success', 'Account created! You can now log in.');
        header('Location: ' . BASE_URL . '/login');
        exit;
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
 
        if ($role === 'charity') {
            $docError = $this->validateBusinessDoc($_FILES['business_reg_doc'] ?? null);
            if ($docError !== null) {
                $errors['business_reg_doc'] = $docError;
            }
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
 
        require __DIR__ . '/../Views/auth/login.php';
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

        Session::set('user_id', (int) $user['id']);
        Session::set('user_role', $user['role']);
        Session::set('user_email', $user['email']);

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
            'admin'    => BASE_URL . '/', // TODO: point at the admin dashboard once it's built
        ];
 
        header('Location: ' . ($destinations[$user['role']] ?? BASE_URL . '/'));
        exit;
    }

    public function logout(): void
    {
        Session::forget('user_id');
        Session::forget('user_role');
        Session::forget('user_email');
        Session::forget('user');
        header('Location: ' . BASE_URL . '/');
        exit;
    }
}
