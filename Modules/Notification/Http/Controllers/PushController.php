<?php
namespace Modules\Notification\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Notification\Entities\BroadcastMessage;
use Modules\Notification\Entities\UnicastMessage;
use Modules\Notification\Http\Requests\Push\PushUnicastRequest;
use Modules\Notification\Jobs\BroadcastJob;
use Modules\Notification\Jobs\UnicastUserJob;
use Modules\Notification\Repositories\PushNotification\PushNotificationInterface;

/**
 * Class PushController
 * @package Modules\Notification\Http\Controllers
 * @author DaiDP
 * @since Aug, 2020
 */
class PushController extends Controller
{
    /**
     * @var PushNotificationInterface
     */
    protected $noti;

    /**
     * PushController constructor.
     * @param PushNotificationInterface $noti
     */
    public function __construct(PushNotificationInterface $noti)
    {
        $this->noti = $noti;
    }

    /**
     * Gửi notification cho 1 user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unicastAction(PushUnicastRequest $request)
    {
        $message = new UnicastMessage($request->all());
        $job = new UnicastUserJob($message);
        dispatch($job);

        return $this->responseJson(CODE_SUCCESS);
    }

    /**
     * Gửi notification cho tất cả user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function broadcastAction(Request $request)
    {
        $message = new BroadcastMessage([
            'title' => 'test',
            'message' => 'mot con vit xoe ra hai cai canh'
        ]);
        $job = new BroadcastJob($message);
        dispatch($job);

        return $this->responseJson(CODE_SUCCESS);
    }
}
