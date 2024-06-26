<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', 'HomeController@index');
Route::post('/data', 'HomeController@data');
Route::get('/download', 'HomeController@download');
Route::post('/store', 'HomeController@store');
Route::delete('/delete/{id}', 'HomeController@delete');

Route::get('/xoc-dia', 'XocDiaController@index');
Route::post('/xoc-dia/data', 'XocDiaController@data');
Route::get('/xoc-dia/download', 'XocDiaController@download');
Route::post('/xoc-dia/store', 'XocDiaController@store');
Route::delete('/xoc-dia/delete/{id}', 'XocDiaController@delete');
