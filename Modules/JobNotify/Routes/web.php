<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('job-notify')->group(function() {
    Route::get('/', 'JobNotifyController@index');

    //Trigger job thông báo
    Route::post('trigger-send-notify', 'JobNotifyController@triggerSendNotify');
});
