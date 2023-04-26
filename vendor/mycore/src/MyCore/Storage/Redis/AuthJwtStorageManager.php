<?php
namespace MyCore\Storage\Redis;

/**
 * Interface AuthJwtStorageManager
 * @package MyCore\Storage\Redis
 * @author DaiDP
 * @since Aug, 2019
 */
interface AuthJwtStorageManager
{
    /**
     * Save token to redis
     *
     * @param $token
     * @return mixed
     */
    public function saveToken($token);

    /**
     * Check token valid or not
     *
     * @param $token
     * @return mixed
     */
    public function checkToken($token);
}