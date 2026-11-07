<?php
/**
 * app/Core/Geocoder.php
 *
 * Thin wrapper around OpenStreetMap's free Nominatim geocoding service.
 * Turns a free-text address (e.g. an outlet's branch_location) into
 * { lat, lng }, so it can be compared against a customer's location with
 * the Haversine formula.
 *
 * No API key or billing account needed — Nominatim's public endpoint is
 * free to use. It does ask that requests carry a descriptive User-Agent
 * and stay under 1 request/second, which is easily fine for our usage
 * (registration-time geocoding + a one-time backfill script).
 *
 * Used by:
 *   - app/Models/Outlet.php        (geocode on register / profile update)
 *   - database/geocode_existing_outlets.php  (one-time backfill script)
 */
class Geocoder
{
    /**
     * @return array{lat: float, lng: float}|null  null on failure —
     *         callers should treat that outlet as "no known location"
     *         rather than block the save.
     */
    public static function geocode(string $address): ?array
    {
        $address = trim($address);
        if ($address === '') {
            return null;
        }

        // Bias toward Sri Lanka since that's where this project's
        // outlets are based, without hard-restricting results to it.
        $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
            'q'              => $address,
            'format'         => 'json',
            'limit'          => 1,
            'countrycodes'   => 'lk',
        ]);

        $context = stream_context_create([
            'http' => [
                // Nominatim's usage policy requires a descriptive
                // User-Agent identifying the application.
                'header'  => "User-Agent: 2ndHarvest-App/1.0 (student project)\r\n",
                'timeout' => 5,
            ],
        ]);

        try {
            $raw = file_get_contents($url, false, $context);
        } catch (\Throwable $e) {
            error_log('Geocoder: request failed — ' . $e->getMessage());
            return null;
        }

        if ($raw === false) {
            error_log('Geocoder: request failed for address "' . $address . '"');
            return null;
        }

        $data = json_decode($raw, true);
        if (empty($data[0]['lat']) || empty($data[0]['lon'])) {
            error_log('Geocoder: no result for "' . $address . '"');
            return null;
        }

        return [
            'lat' => (float) $data[0]['lat'],
            'lng' => (float) $data[0]['lon'],
        ];
    }
}
