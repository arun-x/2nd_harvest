<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../Core/Geocoder.php';

class Outlet extends BaseModel
{
    public static function create(array $data): int
    {
        // Best-effort: if geocoding fails (bad address, no API key yet,
        // network hiccup), the outlet is still created — it just won't
        // show up in the 30km radius filter until re-saved successfully.
        $geo = Geocoder::geocode($data['branch_location']);

        $sql = 'INSERT INTO outlets (user_id, outlet_name, branch_location, region, latitude, longitude)
                VALUES (:user_id, :outlet_name, :branch_location, :region, :latitude, :longitude)';
        $stmt = self::db()->prepare($sql);
        $stmt->execute([
            ':user_id'         => $data['user_id'],
            ':outlet_name'     => $data['outlet_name'],
            ':branch_location' => $data['branch_location'],
            ':region'          => $data['region'],
            ':latitude'        => $geo['lat'] ?? null,
            ':longitude'       => $geo['lng'] ?? null,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function findByUserId(int $userId): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM outlets WHERE user_id = :uid LIMIT 1');
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function updateProfile(int $id, array $data): bool
    {
        // Re-geocode whenever the address changes so the radius filter
        // stays accurate; keep the old coordinates if this lookup fails.
        $geo = Geocoder::geocode($data['branch_location']);

        $sql = 'UPDATE outlets
                SET outlet_name = :outlet_name, branch_location = :branch_location, region = :region';
        $params = [
            ':outlet_name'     => $data['outlet_name'],
            ':branch_location' => $data['branch_location'],
            ':region'          => $data['region'],
            ':id'              => $id,
        ];
        if ($geo !== null) {
            $sql .= ', latitude = :latitude, longitude = :longitude';
            $params[':latitude']  = $geo['lat'];
            $params[':longitude'] = $geo['lng'];
        }
        $sql .= ' WHERE id = :id';

        $stmt = self::db()->prepare($sql);
        return $stmt->execute($params);
    }
}
