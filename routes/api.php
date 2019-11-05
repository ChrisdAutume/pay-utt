<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('v1')->group(function(){
    Route::prefix('transaction')->group(function(){
        Route::put('/{InitialisedTransactionUUID}/selfUpdate', ['as' => 'api.v1.transaction.selfUpdate', 'uses' => 'Api\TransactionController@selfUpdate']);
    });
    Route::prefix('auth')->group(function(){
        Route::post('login', 'AuthController@login');
        Route::get('login', 'AuthController@login');
        Route::post('logout', 'AuthController@logout');
        Route::post('refresh', 'AuthController@refresh');
        Route::post('me', 'AuthController@me');
        Route::get('me', 'AuthController@me');
    });
});
