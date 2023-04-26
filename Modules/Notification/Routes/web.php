<?php
Route::group(['prefix' => 'notification', 'middleware' => ['trust_ip', 'multi_tenant']], function () {
    Route::post('register', 'RegisterController@indexAction');
    Route::post('push', 'PushController@unicastAction');
    Route::post('all', 'PushController@broadcastAction');


});
