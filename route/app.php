<?php


Route::get('/','Api\Test@index');
Route::group(
    ['middleware' => [
      'middleware\Check'
    ]],
    function(){
        Route::get('/index','Api\Test@index');
    }
);

Route::get('/baidu','Api\Test@test');



















