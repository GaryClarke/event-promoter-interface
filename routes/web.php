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

Route::get('/invitations/{code}', 'InvitationsController@show')->name('invitations.show');

Route::post('/register', 'Auth\RegisterController@register')->name('auth.register');

Route::group(['middleware' => 'auth', 'prefix' => 'backstage', 'namespace' => 'Backstage'], function() {

    Route::get('/concerts/new', 'ConcertsController@create')->name('backstage.concerts.new');

    Route::post('/concerts', 'ConcertsController@store');

    Route::get('/concerts/{id}/edit', 'ConcertsController@edit')->name('backstage.concerts.edit');

    Route::patch('/concerts/{id}', 'ConcertsController@update')->name('backstage.concerts.update');

    Route::get('concerts', 'ConcertsController@index')->name('backstage.concerts.index');

    Route::post('/published-concerts', 'PublishedConcertsController@store')->name('backstage.published-concerts.store');

    Route::get('/published-concerts/{id}/orders', 'PublishedConcertOrdersController@index')->name('backstage.published-concert-orders.index');

    Route::get('/concerts/{id}/messages/new', 'ConcertMessagesController@create')->name('backstage.concert-messages.new');

    Route::post('/concerts/{id}/messages', 'ConcertMessagesController@store')->name('backstage.concert-messages.store');
});

