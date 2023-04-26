<?php

namespace Modules\JobNotify\Http\Controllers;

use App\Jobs\FunctionSendNotify;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class JobNotifyController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('jobnotify::index');
    }


    /**
     * Trigger tới job send notify
     *
     * @param Request $request
     */
    public function triggerSendNotify(Request $request)
    {
       FunctionSendNotify::dispatch($request->all());
    }
}
