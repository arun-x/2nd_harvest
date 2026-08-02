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
        $impactStats = [
            'produce_rescued'    => '550+ kg',
            'active_charities' => '45+',
            'supermarket_outlets'  => '96+',
        ];
 
        // No layouts/main.php here — home.php is a full standalone page
        // with its own <html>/<head> and public top nav, not the app shell.
        require __DIR__ . '/../Views/home.php';
    }
}