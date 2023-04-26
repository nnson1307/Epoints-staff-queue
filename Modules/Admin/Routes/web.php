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

Route::group(['prefix' => 'admin', 'middleware' => ['trust_ip']], function () {
    Route::prefix('brand')->group(function() {
        Route::get('/get-all', 'BrandController@getAll');
        Route::post('/get-all', 'BrandController@getAll');
        Route::post('/get-all-by-social', 'BrandController@getAllBySocial');
        Route::get('/get-all-by-social', 'BrandController@getAllBySocial');

        Route::post('get-brand-by-client', 'BrandController@getBrandByClient');
    });
});
