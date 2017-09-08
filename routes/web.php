<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function() {
   return 'Laravel';
});

Route::get('concerts/{id}', 'ConcertsController@show');

Route::post('concerts/{id}/orders', 'ConcertOrdersController@store');

Route::get('orders/{confirmationNumber}', 'OrdersController@show');

Route::post('/login', 'Auth\LoginController@login');

