<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use MyCore\Http\Response\ResponseFormatTrait;

class Controller extends BaseController
{
    const CODE_SUCCESS = CODE_SUCCESS;
    const CODE_ERROR = CODE_ERROR;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ResponseFormatTrait;
}
