<?php
namespace Modules\Notification\Http\Controllers;

use Modules\Notification\Entities\RegisterTokenMessage;
use Modules\Notification\Http\Requests\Register\RegisterTokenRequest;
use Modules\Notification\Jobs\RegisterTokenJob;

/**
 * Class RegisterController
 * @package Modules\Notification\Http\Controllers
 * @author DaiDP
 * @since Aug, 2020
 */
class RegisterController extends Controller
{
    /**
     * Đăng ký device token
     *
     * @param RegisterTokenRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexAction(RegisterTokenRequest $request)
    {
        $message = new RegisterTokenMessage($request->all());
        $job = new RegisterTokenJob($message);
        dispatch($job);

        return $this->responseJson(CODE_SUCCESS);
    }
}
