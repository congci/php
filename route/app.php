<?php

class_alias('facades\Route','Route');
Route::get('/','Api\Test@index');
Route::group(
    ['middleware' => [
      'middleware\Check'
    ]],
    function(){
        Route::get('/index','Api\Test@index');
    }
);



















