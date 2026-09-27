<?php
/**
 * app/Controllers/HomeController.php
 * Serves the public marketing homepage. Deliberately doesn't extend
 * BaseController's auth-aware behavior (if you add any later) since this
 * page must render for logged-out visitors.
 */
 
class HomeController
{
    public function index(): void
    {
        // Live platform totals. The homepage must still render if the
        // database is unreachable, so fall back to a dash rather than error.
        try {
            $counts  = Report::platformCounts();
            $rescued = Report::summary('2000-01-01', date('Y-m-d'))['kg_rescued'];

            $impactStats = [
                'produce_rescued'     => rtrim(rtrim(number_format($rescued, 1), '0'), '.') . ' kg',
                'active_charities'    => number_format($counts['charities']),
                'supermarket_outlets' => number_format($counts['outlets']),
            ];
        } catch (Throwable $e) {
            $impactStats = [
                'produce_rescued'     => '—',
                'active_charities'    => '—',
                'supermarket_outlets' => '—',
            ];
        }

        // No layouts/main.php here — home.php is a full standalone page
        // with its own <html>/<head> and public top nav, not the app shell.
        require __DIR__ . '/../Views/home.php';
    }

    public function contact(): void
    {
        $old     = Session::get('contact_old', []);
        $errors  = Session::get('contact_errors', []);
        $success = Session::get('contact_success', false);
        Session::forget('contact_old');
        Session::forget('contact_errors');
        Session::forget('contact_success');
        $csrfToken = $this->contactCsrfToken();

        require __DIR__ . '/../Views/contact.php';
    }

    public function submitContact(): void
    {
        $sent = $_POST['_csrf'] ?? '';
        if (!is_string($sent) || !hash_equals($this->contactCsrfToken(), $sent)) {
            Session::set('contact_errors', ['form' => 'Your session expired. Please try again.']);
            $this->redirectToContact();
        }

        // Hidden field that people never see; bots that fill it are ignored.
        if (trim($_POST['company'] ?? '') !== '') {
            Session::set('contact_success', true);
            $this->redirectToContact();
        }

        $input = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name'  => trim($_POST['last_name'] ?? ''),
            'email'      => trim($_POST['email'] ?? ''),
            'message'    => trim($_POST['message'] ?? ''),
            'website'    => trim($_POST['website'] ?? ''),
            'phone'      => trim($_POST['phone'] ?? ''),
        ];

        $errors = [];
        foreach (['first_name', 'last_name', 'email', 'message', 'phone'] as $field) {
            if ($input[$field] === '') {
                $errors[$field] = 'This field is required.';
            }
        }
        if (mb_strlen($input['first_name']) > 80) $errors['first_name'] = 'Please keep this under 80 characters.';
        if (mb_strlen($input['last_name']) > 80)  $errors['last_name']  = 'Please keep this under 80 characters.';
        if ($input['email'] !== '' && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }
        if (mb_strlen($input['message']) > 2000) {
            $errors['message'] = 'Please keep your message under 2000 characters.';
        }
        if ($input['phone'] !== '' && !preg_match('/^\+?[0-9 ()-]{7,20}$/', $input['phone'])) {
            $errors['phone'] = 'Enter a valid contact number.';
        }
        if ($input['website'] !== '') {
            if (!preg_match('#^https?://#i', $input['website'])) {
                $input['website'] = 'https://' . $input['website'];
            }
            if (!filter_var($input['website'], FILTER_VALIDATE_URL) || strlen($input['website']) > 255) {
                $errors['website'] = 'Enter a valid website address.';
            }
        }

        if ($errors) {
            Session::set('contact_old', $input);
            Session::set('contact_errors', $errors);
            $this->redirectToContact();
        }

        try {
            ContactMessage::create($input);
        } catch (Throwable $e) {
            Session::set('contact_old', $input);
            Session::set('contact_errors', ['form' => 'We could not send your message. Please try again.']);
            $this->redirectToContact();
        }

        Session::set('contact_success', true);
        $this->redirectToContact();
    }

    private function contactCsrfToken(): string
    {
        $token = Session::get('contact_csrf');
        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set('contact_csrf', $token);
        }
        return $token;
    }

    private function redirectToContact(): void
    {
        header('Location: ' . BASE_URL . '/contact');
        exit;
    }
}
