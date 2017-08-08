<?php

/**
 *  路由模块
 * Class Route
 */
namespace kernel;
use ReflectionClass;
use Closure;


class Route
{
    protected  static $data = [];
    protected $middleware = [];
    protected  $middlePart = [];

    protected $method = [
        'GET',
        'POST',
    ];




    public function requires(){
        self::$data = [];
        foreach (glob('route/*') as $v){
            $name = $this->getFileName($v);
            require $v;
        }
    }


    protected function getFileName($filename){
        return substr($filename,0,strpos($filename,'.'));
    }

    protected static function slice($fileMethod){
        list($class,$method) = explode('@',$fileMethod);
        $class = 'app\\Controller\\' .$class;
        return [
            'class'=>$class,
            'method'=>$method
        ];


    }


    public function getAll(){
        return self::$data;
    }

    public function get($path,$fileMethod,$middleware = []){
        $this->addRoute('GET',$path,$fileMethod,$middleware);
    }


    public function post($path,$fileMethod,$middleware = []){
        $this->addRoute('POST',$path,$fileMethod,$middleware);
    }


    protected function addRoute($reMethod,$path,$fileMethod,$middleware = []){
        $middleware = $middleware + $this->middlePart;
        if(!$classMethod = self::slice($fileMethod)){
            return false;
        }
        self::$data[$reMethod][] = [
            'path'   => $path,
            'class'  => $classMethod['class'],
            'method' => $classMethod['method'],
            'middleware' => $middleware
        ];
    }



    public function match($request){
        $matchRoute = [];
        $requestMethod = $request->requestMethod;
        $path = $request->path;
        foreach (self::$data[$requestMethod] as $v){
            if($v['path'] == $path){
                $matchRoute = $v;
                break;
            }
        }
        if(empty($matchRoute)){
            return false;
        }
        $request->class = $matchRoute['class'];
        $request->method = $matchRoute['method'];
        return $matchRoute;
    }




    public function dispatch(){
        return function($request){
            $route = $this->match($request);
            if(!$route){
                return false;
            }
            return $this->middleRoute($route,$request);
        };
    }

    private function middleRoute($route,$request){
        //执行中间件
        $middleware = $route['middleware'] + $this->middleware;
        return  (new Pipeline())
            ->send($request)
            ->through($middleware)
            ->then($this->exec());

    }

    /**
     * exec
     * @param $route
     * @return Closure
     */
    protected function exec(){
        return function($request){
            $class = $request->class;
            $ref = new  ReflectionClass($class);
            if(!$ref->getMethod($request->method)->isStatic()){
                $class = new $class;
            }
            return call_user_func(array($class,$request->method),$request);
        };
    }

    /**
     * @param $plug
     * @param $func
     * 因为是依次执行
     */
    public function group($plug,$func){
        if(is_array($plug)){
            if(isset($plug['middleware'])){
                $this->middlePart = $plug['middleware'];
            }
        }
        if($func instanceof Closure){
            $func();
        }
        $this->middlePart = [];
    }




}
