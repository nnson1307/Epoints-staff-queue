<?php
namespace MyCore\Repository;

/**
 * Created by PhpStorm.
 * User: phuoc
 * Date: 1/3/2019
 * Time: 4:36 PM
 */
class DummyRepository
{
    public function __call($method, $params)
    {
        echo 'Đại đẹp trai';
    }
}