<?php
namespace MyCore\SyncStrava;

use App\Jobs\StravaSyncDataJob;
use App\Models\SyncDataMessage;
use Illuminate\Support\Facades\Redis;

/**
 * Class SyncActivity
 * @package MyCore\SyncStrava
 * @author DaiDp
 * @since Aug, 2019
 */
class SyncActivity
{
    /**
     * Connection để hạn chế thời gian sync data
     *
     * @var mixed
     */
    protected $connection;

    /**
     * Thời gian quy định bao lâu mới được sync 1 lần
     *
     * @var mixed
     */
    protected $limitTime;


    /**
     * SyncActivity constructor.
     */
    public function __construct()
    {
        $this->connection = env('FOXSTEPS_SYNC_REDIS', 'default');
        $this->limitTime = env('FOXSTEPS_SYNC_TIME', 1800);
    }

    /**
     * Gọi đồng bộ
     *
     * @param $idUser
     * @throws SyncActivityException
     */
    public function syncStravaActivity($idUser)
    {
        $key = 'sync:' . $idUser;
        // Get tạo redis connection
        $redis = Redis::connection($this->connection);

        // Check xem có tồn tại key redis thì văng exception
        if ($redis->get($key)) {
            $time = $redis->ttl($key);
            throw new SyncActivityException('Bạn đồng bộ quá nhanh. Vui lòng thử lại sau ' . $this->convertTimeToString($time));
        }

        // add key redis expire. Để cái này trước, không thôi sẽ bị cái queue redis làm ảnh hưởng
        $redis->pipeline(function ($pipe) use ($key) {
            $pipe->set($key, 1);
            $pipe->expire($key, $this->limitTime);
        });

        $this->triggerSync($redis, $idUser);
    }

    /**
     * Gọi queue sync. Mục đích là thông báo ngta 30 phút chứ thực tế là 2 tiếng mới cho đồng bộ lại
     *
     * @param \Illuminate\Redis\Connections\Connection $redis
     * @param $idUser
     */
    protected function triggerSync($redis, $idUser)
    {
        $key = 'real_sync:' . $idUser;

        // Check xem có tồn tại key redis thì văng exception
        if ($redis->get($key)) {
            return;
        }

        // add key redis expire. Để cái này trước, không thôi sẽ bị cái queue redis làm ảnh hưởng
        $redis->pipeline(function ($pipe) use ($key) {
            $pipe->set($key, 1);
            $pipe->expire($key, 7200);
        });

        // Dispatch job sync
        $sync = new StravaSyncDataJob(new SyncDataMessage(['user_id' => $idUser]));
        dispatch($sync);
    }


    /**
     * Convert thời gian sang string
     *
     * @param $time
     * @return string
     */
    protected function convertTimeToString($time)
    {
        $m = floor($time / 60);
        $s = $time % 60;
        $str = '';

        if ($m > 0) {
            $str .= $m . ' phút ';
        }

        if ($s > 0) {
            $str .= $s . ' giây';
        }

        return $str;
    }
}