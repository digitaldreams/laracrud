<?php
Route::group(['middleware' => ['web'],'as'=>'@@packageName@@::', 'namespace' => '@@packageNamespace@@\Http\Controllers'], function () {
    //your routes here
});