<?php

class_alias('\kernel\Facades\Route','Route');
Route::get('/','Api\Test@index');
Route::group(
    ['middleware' => [
      'middleware\Check'
    ]],
    function(){
        Route::get('/index','Api\Test@index');
    }
);



















