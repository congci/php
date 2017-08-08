<?php

namespace kernel\Facades;

class Facades
{


    public static function __callStatic($method,$arguments){
        $instance =  new \kernel\Route;
        return call_user_func_array([$instance,$method],$arguments);

    }

    public function __call($method,$arguments){
        $instance =  self::getInstance();
        return call_user_func_array([$instance,$method],$arguments);
    }





}