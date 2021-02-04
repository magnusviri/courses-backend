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

ini_set('memory_limit', '256M');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

JsonApi::register('default')->routes(function ($api) {
    $api->resource('attrs')->relationships(function ($relations) {
        $relations->hasMany('courses');
    });
    $api->resource('courses')->relationships(function ($relations) {
        $relations->hasMany('attrs');
        $relations->hasOne('description');
        $relations->hasMany('instructors');
        $relations->hasMany('meets_with');
        $relations->hasOne('special');
        $relations->hasMany('when_where');
    });
    $api->resource('descriptions')->relationships(function ($relations) {
        $relations->hasMany('courses');
    });
    $api->resource('instructors')->relationships(function ($relations) {
        $relations->hasMany('courses');
    });
    $api->resource('meets_with')->relationships(function ($relations) {
        $relations->hasMany('courses');
    });
    $api->resource('specials')->relationships(function ($relations) {
        $relations->hasMany('courses');
    });
    $api->resource('when_where')->relationships(function ($relations) {
        $relations->hasMany('courses');
    });
});
