<?php
/**
 * Class Facade
 * @package kernel\Facades
 */
namespace kernel\Facades;
use ReflectionClass;


class Facade
{


    public static function __callStatic($method,$arguments){
        $class = static::getInstance();
        $ref = new  ReflectionClass($class);
        if(!$ref->getMethod($method)->isStatic()){
            $class = new $class;
        }
        return call_user_func_array([$class,$method],$arguments);

    }

    public function __call($method,$arguments)
    {
        $class = static::getInstance();
        $ref = new  ReflectionClass($class);
        if (!$ref->getMethod($method)->isStatic()) {
            $class = new $class;
        }
        return call_user_func_array([$class, $method], $arguments);


    }

 }