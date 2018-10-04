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

Route::get('/install' , 'InstallationController@start');
Route::get('/install/step1' , 'InstallationController@step1');
Route::get('/install/step2' , 'InstallationController@step2');
Route::get('/install/step3' , 'InstallationController@step3');
Route::post('/install/step1/save' , 'InstallationController@step1Save');
Route::post('/install/step2/save' , 'InstallationController@step2Save');
Route::post('/install/step3/save' , 'InstallationController@step3Save');

Route::get('/update' , 'InstallationController@update');
Route::post('/update/save' , 'InstallationController@updateSave');

Route::any('/{blabla?}' , function()
{
	return redirect('/install');
})->where('blabla' , '.+');