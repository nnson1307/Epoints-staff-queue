<?php
namespace MyCore\Repository;

/**
 * Created by PhpStorm.
 * User: phuoc
 * Date: 12/14/2018
 * Time: 4:55 PM
 */
trait LocationTrait
{
    /**
     * Tạo tọa độ
     *
     * @param $latitude
     * @param $longitude
     * @return string
     */
    protected function getGeoLocation($latitude, $longitude)
    {
        return sprintf('%s;%s', $latitude, $longitude);
    }
}