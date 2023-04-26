<?php
namespace MyCore\Repository;

/**
 * Created by PhpStorm.
 * User: phuoc
 * Date: 1/3/2019
 * Time: 3:13 PM
 */
trait ExtractData
{
    /**
     * Lấy các giá trị các field
     *
     * @param array $data
     * @param array $keys
     * @return array|mixed|null
     */
    public function only(array $data, array $keys)
    {
        $result = [];

        foreach ($keys as $k)
        {
            $result[$k] = $data[$k] ?? null;
        }

        return $result;
    }
}