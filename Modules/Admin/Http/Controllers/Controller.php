<?php
namespace Modules\Admin\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelController;
use MyCore\Http\Response\ResponseFormatTrait;

/**
 * Class Controller
 * @package Modules\Notification\Http\Controllers
 * @author DaiDP
 * @since Aug, 2020
 */
abstract class Controller extends LaravelController
{
    use ResponseFormatTrait, ValidatesRequests;
}
