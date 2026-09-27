<?php
namespace App\Models;

class Outlet extends BaseModel
{
    protected string $table = 'outlets';

    /**
     * Outlets within $radiusKm of ($lat, $lng), nearest first.
     * Outlets with no geocoded coordinates yet are excluded — they
     * simply won't appear in the marketplace's location filter until
     * geocoded (see database/geocode_existing_outlets.php).
     *
     * Uses the Haversine formula directly in SQL. A bounding-box
     * pre-filter (WHERE) keeps this fast even as the outlets table
     * grows, since it can use the idx_outlets_geo index before the
     * more expensive trig on every remaining row.
     *
     * @return array<int, array{id:int, outlet_name:string, branch_location:string, distance_km:float}>
     */
    public function findNearby(float $lat, float $lng, float $radiusKm = 30): array
    {
        // ~1 degree of latitude is ~111km; pad slightly for longitude
        // shrinking at higher latitudes so the box never excludes a
        // real match before the precise Haversine check below.
        $latPad = $radiusKm / 110.574;
        $lngPad = $radiusKm / (111.320 * max(cos(deg2rad($lat)), 0.01));

        $sql = "SELECT id, outlet_name, branch_location, region, latitude, longitude,
                  (6371 * ACOS(
                    LEAST(1, GREATEST(-1,
                      COS(RADIANS(:lat1)) * COS(RADIANS(latitude)) *
                      COS(RADIANS(longitude) - RADIANS(:lng1)) +
                      SIN(RADIANS(:lat2)) * SIN(RADIANS(latitude))
                    ))
                  )) AS distance_km
                FROM outlets
                WHERE latitude  IS NOT NULL
                  AND longitude IS NOT NULL
                  AND latitude  BETWEEN :lat_min AND :lat_max
                  AND longitude BETWEEN :lng_min AND :lng_max
                HAVING distance_km <= :radius
                ORDER BY distance_km ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'lat1'    => $lat,
            'lat2'    => $lat,
            'lng1'    => $lng,
            'lat_min' => $lat - $latPad,
            'lat_max' => $lat + $latPad,
            'lng_min' => $lng - $lngPad,
            'lng_max' => $lng + $lngPad,
            'radius'  => $radiusKm,
        ]);

        return $stmt->fetchAll();
    }
}
