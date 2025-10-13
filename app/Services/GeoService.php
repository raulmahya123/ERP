<?php
// app/Services/GeoService.php

declare(strict_types=1);

namespace App\Services;

/**
 * GeoService
 *
 * Utilitas geospasial sederhana untuk menghitung jarak Haversine (meter)
 * dan helper terkait geofence.
 *
 * PHP 8.2 / Laravel 10+
 */
final class GeoService
{
    /**
     * Hitung jarak permukaan bumi (great-circle) dengan rumus Haversine.
     *
     * @param  float $lat1  Latitude titik A (derajat, -90..90)
     * @param  float $lng1  Longitude titik A (derajat, -180..180)
     * @param  float $lat2  Latitude titik B (derajat, -90..90)
     * @param  float $lng2  Longitude titik B (derajat, -180..180)
     * @return int          Jarak dalam meter (dibulatkan ke meter terdekat)
     */
    public static function distance(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        // Radius bumi dalam meter
        $R = 6371000.0;

        $φ1 = self::toRad($lat1);
        $φ2 = self::toRad($lat2);
        $Δφ = self::toRad($lat2 - $lat1);
        $Δλ = self::toRad($lng2 - $lng1);

        $a = \sin($Δφ / 2) ** 2
           + \cos($φ1) * \cos($φ2) * \sin($Δλ / 2) ** 2;

        // Lindungi dari error floating point di batas (>1 atau <0)
        $a = max(0.0, min(1.0, $a));

        $c = 2 * \asin(\sqrt($a));
        $d = $R * $c;

        return (int) \round($d);
    }

    /**
     * Cek apakah koordinat (lat,lng) berada di dalam geofence lingkaran.
     *
     * @param  float $lat         Latitude titik (derajat)
     * @param  float $lng         Longitude titik (derajat)
     * @param  float $centerLat   Latitude pusat geofence
     * @param  float $centerLng   Longitude pusat geofence
     * @param  int   $radiusMeter Radius geofence (meter)
     * @return bool
     */
    public static function insideGeofence(
        float $lat,
        float $lng,
        float $centerLat,
        float $centerLng,
        int $radiusMeter
    ): bool {
        return self::distance($lat, $lng, $centerLat, $centerLng) <= $radiusMeter;
    }

    /**
     * Helper: derajat → radian.
     */
    public static function toRad(float $deg): float
    {
        return $deg * M_PI / 180.0;
    }

    /**
     * Helper: radian → derajat.
     */
    public static function toDeg(float $rad): float
    {
        return $rad * 180.0 / M_PI;
    }
}
