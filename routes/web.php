<?php

use Illuminate\Http\Request;

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

Route::get('/', 'HomeController@index');
Route::get('/action', 'HomeController@index')->middleware('auth.redirect');
Route::get('/json/accountInfo', 'HomeController@accountInfo');

//Route::options('/jsonp/newQqUser', 'QQAuthController@newQqUserOptions');
//Route::get('/auth/qq/create', 'QQAuthController@showCreateForm');
Route::get('/auth/qq', 'QQAuthController@code');
Route::get('/auth/qq/url', 'HomeController@qqAuthUrl');
Route::get('/auth/qq/info', 'QQAuthController@qqUserInfo');
Route::get('/auth/qq/create', 'QQAuthController@index');
Route::post('/auth/qq/create', 'QQAuthController@register');

Route::get('/json/myActionList', 'ActionManager@myActionList');
Route::get('/json/myCategoryList', 'ActionManager@myCategoryList');
Route::post('/json/addCategory', 'ActionManager@addCategory');
Route::post('/json/saveCategory', 'ActionManager@saveCategory');
Route::post('/json/deleteCategory', 'ActionManager@deleteCategory');
Route::post('/json/addAction', 'ActionManager@addAction');
Route::post('/json/saveAction', 'ActionManager@saveAction');
Route::post('/json/deleteAction', 'ActionManager@deleteAction');

Route::post('/json/do', 'RecordManager@changeCurrent');
Route::get('/json/current', 'RecordManager@current');
Route::get('/json/recordHistory/{num?}', 'RecordManager@history')->where('num', '\d+');

// Authentication Routes...
Route::post('login', 'Auth\LoginController@login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

// Registration Routes...
Route::post('register', 'Auth\RegisterController@register');

// Password Reset Routes...
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm');
Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');
