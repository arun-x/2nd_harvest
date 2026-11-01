<?php
/**
 * database/geocode_existing_outlets.php
 *
 * One-time backfill: finds every outlet with no latitude/longitude yet
 * (i.e. it was registered before the geo columns existed) and geocodes
 * its branch_location. Safe to re-run — it only touches rows that are
 * still NULL.
 *
 * Run from the project root:
 *   php database/geocode_existing_outlets.php
 */

require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Geocoder.php';

// Geocoder::geocode() now uses OpenStreetMap's free Nominatim service —
// no API key or config needed.

$db = Database::connection();
$stmt = $db->query('SELECT id, outlet_name, branch_location FROM outlets WHERE latitude IS NULL OR longitude IS NULL');
$outlets = $stmt->fetchAll();

if (!$outlets) {
    echo "Nothing to do — every outlet already has coordinates.\n";
    exit(0);
}

echo 'Found ' . count($outlets) . " outlet(s) without coordinates.\n";

$update = $db->prepare('UPDATE outlets SET latitude = :lat, longitude = :lng WHERE id = :id');

foreach ($outlets as $outlet) {
    $geo = Geocoder::geocode($outlet['branch_location']);

    if ($geo === null) {
        echo "  [FAILED]  #{$outlet['id']} {$outlet['outlet_name']} — could not geocode \"{$outlet['branch_location']}\"\n";
        continue;
    }

    $update->execute([
        ':lat' => $geo['lat'],
        ':lng' => $geo['lng'],
        ':id'  => $outlet['id'],
    ]);

    echo "  [OK]      #{$outlet['id']} {$outlet['outlet_name']} — {$geo['lat']}, {$geo['lng']}\n";

    // Nominatim's usage policy asks for max ~1 request/second — this
    // pause keeps a backfill of any size comfortably under that.
    usleep(1100000);
}

echo "Done.\n";
