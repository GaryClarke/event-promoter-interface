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

Route::get('concerts/{id}', 'ConcertsController@show')->name('concerts.show');

Route::post('concerts/{id}/orders', 'ConcertOrdersController@store');

Route::get('orders/{confirmationNumber}', 'OrdersController@show');

Route::get('login', 'Auth\LoginController@getLogin')->name('auth.show-login');

Route::post('/login', 'Auth\LoginController@login')->name('auth.login');

Route::post('/logout', 'Auth\LoginController@logout')->name('auth.logout');

Route::group(['middleware' => 'auth', 'prefix' => 'backstage', 'namespace' => 'Backstage'], function() {

    Route::get('concerts', 'ConcertsController@index')->name('backstage.concerts.index');

    Route::get('/concerts/new', 'ConcertsController@create')->name('backstage.concerts.new');

    Route::post('/concerts', 'ConcertsController@edit')->name('backstage.concerts.edit');

    Route::post('/concerts/{id}/edit', 'ConcertsController@edit')->name('backstage.concerts.edit');

    Route::post('/concerts', 'ConcertsController@store');

    Route::patch('/concerts/{id}', 'ConcertsController@update')->name('backstage.concerts.update');
});




