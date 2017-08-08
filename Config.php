<?php
/**
 * info 配置模块
 *
 */

class Config
{
    private static $instance;
    private static $data = []; //配置存储

    protected $serverFile = 'config/*.conf';
    protected $normalFile = 'config/*.php';


    /**
     * info 获取单例
     */
    public static function getInstance(){
        if(!self::$instance instanceof self){
            self::$instance = new self();
        }
        return self::$instance;
    }



    /**
     * info 引入数据
     */
    public function requires(){
        self::$data = [];
        foreach (glob('config/*.php') as $v){
             self::$data += require $v;
        }
        $serves = [];
        foreach (glob($this->serverFile) as $v){
            $serves += parse_ini_file($v,true) ;
        }
        self::$data['process']= $serves;
    }

    /**
     * info get config
     * @param $name
     * @return mixed
     */
    public function get($name,$default = '.'){
        if(strpos($name,$default)){
            $ret = self::$data;
            $nameArr = explode('.',$name);
            foreach ($nameArr as $key){
                if (!isset($ret[$key])) return $default;
                $ret = $ret[$key];
            }
            return $ret;
        }else{
            return self::$data[$name];
        }
    }

    public function getAll(){
        return self::$data;
    }

    /**
     *
     * set config
     * @param $name
     * @param $value
     * @param string $delimiter
     */
    public function set($name,$value,$delimiter = '.'){
        if(!strpos($name,'.')){
            self::$data[$name] = $value;
        }
        $name = explode($delimiter, $name);
        $cnt = count($name);
        for ($i = 0; $i < $cnt - 1; $i ++) {
            if (!isset($pos[$name[$i]])) $pos[$name[$i]] = array();
            $pos = & $pos[$name[$i]];
        }
        $key = $name[$cnt - 1];
        $pos[$key] = $value;
        self::$data = array_merge(self::$data,$pos);
    }

}




