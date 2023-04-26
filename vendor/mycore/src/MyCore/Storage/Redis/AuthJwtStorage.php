<?php
namespace MyCore\Storage\Redis;

use Illuminate\Support\Facades\Redis;

/**
 * Class AuthJwtStorage
 * @package MyCore\Storage\Redis
 * @author DaiDP
 * @since Aug, 2019
 */
class AuthJwtStorage implements AuthJwtStorageManager
{
    protected $connection;
    protected $limitQueue;

    public function __construct()
    {
        $this->connection = env('JWT_REDIS_CONNECTION', 'default');
        $this->limitQueue = env('JWT_REDIS_QUEUE_LIMIT', 10);
    }

    /**
     * Save token to redis
     *
     * @param $token
     * @return mixed
     */
    public function saveToken($token)
    {
        $key  = $this->buildKey($token);
        $conn = $this->getRedisConn();
        $conn->lpush($key, $token);
        $conn->ltrim($key, 0, $this->limitQueue);
        $conn->expire($key, 300);
    }

    /**
     * Check token valid or not
     *
     * @param $token
     * @return mixed
     */
    public function checkToken($token)
    {
        $key  = $this->buildKey($token);
        $conn = $this->getRedisConn();

        $data = $conn->lrange($key, 0, $this->limitQueue);

        return in_array($token, $data);
    }

    /**
     * Redis connection
     *
     * @return \Illuminate\Redis\Connections\Connection
     */
    protected function getRedisConn()
    {
        return Redis::connection($this->connection);
    }

    /**
     * Build redis key
     *
     * @param $token
     * @return string
     */
    protected function buildKey($token)
    {
        $data = explode('.', $token);
        $data = json_decode(base64_decode($data[1]));

        $idUser = $data->sub;
        $session = $data->prv;

        return sprintf('user:%s:%s', $idUser, $session);
    }
}