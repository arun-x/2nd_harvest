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
}
