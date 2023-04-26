<?php
namespace MyCore\Helper;

/**
 * Trait CalcGEODistance
 * @package MyCore\Helper
 * @author DaiDP
 * @since Apr, 2020
 */
trait CalcGEODistance
{
    /**
     * Khoản cách 2 tọa độ. Tính bằng mét
     *
     * @param $lat1
     * @param $lon1
     * @param $lat2
     * @param $lon2
     * @return int
     */
    protected function distanceGEO($lat1, $lon1, $lat2, $lon2)
    {
        // Nếu 1 trong các params is null hoặc không phải là số thì trả về không xác định
        if (!is_numeric($lat1) || !is_numeric($lon1) || !is_numeric($lat2) || !is_numeric($lon2)) {
            return null;
        }

        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        }

        $earthRadius = 6371000;
        // convert from degrees to radians
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return intval($angle * $earthRadius);
    }
}
