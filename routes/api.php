<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

JsonApi::register('default')->routes(function ($api) {
    $api->resource('courses')->relationships(function ($relations) {
        $relations->hasMany('instructors');
        $relations->hasMany('attributes');
    });
    $api->resource('instructors')->relationships(function ($relations) {
        $relations->hasMany('courses');
    });
    $api->resource('attributes')->relationships(function ($relations) {
        $relations->hasMany('courses');
    });
});
