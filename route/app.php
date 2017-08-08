<?php


Route::get('/','Api\Test@index');
Route::group(
    ['middleware' => []],
    function(){
        Route::get('/index','Api\Test@index');
    }
);



















