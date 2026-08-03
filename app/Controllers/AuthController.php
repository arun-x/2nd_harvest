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
                'image' => '/assets/images/register-supermarket.jpg',
            ],
            'charity' => [
                'heading' => 'Make a difference. Register your Charity.',
                'desc' => 'Connect directly with local supermarkets to secure quality surplus food. Power your meal programs and support families in need while effortlessly tracking your rescue impact.',
                'benefits' => [
                    ['title' => 'Easy Donation Tracking', 'desc' => 'Coordinate food pick-ups and log donation sizes directly within your portal.'],
                    ['title' => 'Community Impact Reports', 'desc' => 'Generate live, shareable social and environmental metrics for your donors.'],
                ],
                'image' => '/assets/images/register-charity.jpg',
            ],
            'consumer' => [
                'heading' => 'Join the movement to end food waste.',
                'desc' => "Whether you're a partner outlet or a consumer looking for fresh surplus, 2nd Harvest connects you to what matters.",
                'benefits' => [
                    ['title' => 'Eco-Friendly', 'desc' => 'Reduce your carbon footprint with every basket rescued.'],
                    ['title' => 'Community Driven', 'desc' => 'Support local businesses and help neighbors in need.'],
                ],
                'image' => '/assets/images/register-consumer.jpg',
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
            header('Location: /register');
            exit;
        }
 
        $errors = $this->validateRegistration($role, $_POST);
 
        if (!empty($errors)) {
            Session::set('register_old', $_POST);
            Session::set('register_errors', $errors);
            header('Location: /register/' . $role);
            exit;
        }
 
        // TODO: once Models/User.php exists, replace this with a real save:
        //   $this->userModel->create([
        //       'role' => $role,
        //       'email' => $_POST['email'],
        //       'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
        //       ...role-specific fields...
        //   ]);
        Session::flash('success', 'Account created! You can now log in.');
        header('Location: /login');
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
 
        if ($email === '' || $password === '') {
            Session::set('login_error', 'Enter your email and password.');
            Session::set('login_old_email', $email);
            Session::set('login_old_role', $role);
            header('Location: /login');
            exit;
        }
 
        // TODO: once Models/User.php exists, replace this with a real check:
        //   $user = $this->userModel->findByEmail($email);
        //   if (!$user || !password_verify($password, $user->password_hash)) {
        //       Session::set('login_error', 'Incorrect email or password.');
        //       ... redirect back ...
        //   }
        Session::set('user_role', $role);
        Session::set('user_email', $email);
 
        $destinations = [
            'supermarket' => '/employee/dashboard',
            'charity'     => '/', // TODO: point at the charity dashboard once it's built
            'consumer'    => '/', // TODO: point at the consumer dashboard once it's built
        ];
 
        header('Location: ' . ($destinations[$role] ?? '/'));
        exit;
    }
 
    public function logout(): void
    {
        Session::forget('user_role');
        Session::forget('user_email');
        header('Location: /');
        exit;
    }
}
