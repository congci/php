<?php
namespace kernel\Facades;


class Route extends Facade
{

    public static function getInstance(){
        return \kernel\Route::class;
    }

}