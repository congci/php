<?php

/**
 * Class ThreadPool|线程池
 */

class ConnectPool
{
    protected $connect;
    protected $freenum;
    protected $freeConnect;

    public function __construct()
    {
    }

    /**
     * info 增加
     * mysql  redis
     */
    public function add($host,$socket){
        if(!isset($this->freeConnect[$host])){
            $this->freeConnect[$host] = $socket;
        }
    }

    public function del($host){
        if(isset($this->freeConnect[$host])){
            unset($this->freeConnect[$host]);
        }
    }



    public function get($host){
        if($this->freeConnect[$host]){
            $sock =  $this->freeConnect[$host];
            unset($this->freeConnect[$host]);
            return $sock;
        }

        //如果没有的就创建
        //创建socket
        //数据库 redis


    }


    /**
     * info
     * @param $host
     * @param $socket
     */
    public function release($host,$socket){
        if(!isset($this->freeConnect[$host])){
            $this->freeConnect[$host] = $socket;
        }
    }







}