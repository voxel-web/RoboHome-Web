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

Route::get('/', function () {
    return view('welcome');
});

//https://stackoverflow.com/questions/39196968/laravel-5-3-new-authroutes
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::get('/', 'Web\LoginController@index')->name('index');
Route::get('/login', 'Web\LoginController@login')->name('login');
Route::get('/logout', 'Web\LoginController@logout')->name('logout');

Route::get('/devices', 'Web\DevicesController@devices')->name('devices');
Route::post('/devices/add', 'Web\DevicesController@add')->name('addDevice');
Route::get('/devices/delete/{id}', 'Web\DevicesController@delete')->name('deleteDevice');
Route::post('/devices/update/{id}', 'Web\DevicesController@update')->name('updateDevice');
Route::post('/devices/{action}/{id}', 'Web\DevicesController@handleControlRequest')
    ->where(['action' => '[a-z]+'])
    ->name('handleControlRequest');
