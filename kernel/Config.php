<?php
/**
 * info 配置模块
 *
 */
namespace kernel;

class Config
{

    private  static $data = []; //配置存储

    protected $serverFile = 'config/*.conf';
    protected $normalFile = 'config/*.php';



    /**
     * info 引入数据
     */
    public function requires(){
        self::$data = [];
        foreach (glob('config/*.php') as $v){
            $name = $this->getFileName($v);
            $arr[$name] = require $v;
             self::$data += $arr;
        }

        $serves = [];
        foreach (glob($this->serverFile) as $v){
            $serves += parse_ini_file($v,true);
        }
        self::$data['process']= $serves;
    }

    protected function getFileName($filename){
        $start = strpos($filename,'/')+1;
        $end = strpos($filename,'.');
        $len = $end-$start;
        return substr($filename,$start,$len);
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




