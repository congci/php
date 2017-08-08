<?php

/**
 *  路由模块
 * Class Route
 */
class Route
{
    private static $instance;
    protected  static $data = [];
    protected $middleware = [];
    protected static $middlePart = [];

    protected $method = [
        'GET',
        'POST',
    ];


    /**
     * info 获取单例
     */
    public static function getInstance(){
        if(!self::$instance instanceof self){
            self::$instance = new self();
        }
        return self::$instance;
    }

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

    public static function get($path,$fileMethod,$middleware = []){
        self::addRoute('GET',$path,$fileMethod,$middleware);
    }


    public static function post($path,$fileMethod,$middleware = []){
        self::addRoute('POST',$path,$fileMethod,$middleware);
    }


    protected static function addRoute($reMethod,$path,$fileMethod,$middleware = []){
        $middleware = $middleware + self::$middlePart;
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
                $class = new app\Controller\Api\Test;
            }
            return call_user_func(array($class,'index'),$request);
        };
    }

    /**
     * @param $plug
     * @param $func
     * 因为是依次执行
     */
    public static function group($plug,$func){
        if(is_array($plug)){
            if(isset($plug['middleware'])){
                self::$middlePart = $plug['middleware'];
            }
        }
        if($func instanceof Closure){
            $func();
        }
        self::$middlePart = [];
    }




}
