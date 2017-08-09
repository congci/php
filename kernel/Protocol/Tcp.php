<?php

namespace kernel\Protocol;

class Tcp extends Socket
{
    protected $socket;
    protected $errNo;
    protected $errStr;
    protected $method;
    protected $scheme;
    protected $line;
    protected $header;
    protected $route;
    protected $content;
    protected $request;
    protected $size = 8192;
    protected $params = [];
    protected $headers = [
        'Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'Accept-Encoding'=>'gzip, deflate, br',
        'Connection'=>'keep-alive',
        'Accept-Language'=>'zh-CN,zh;q=0.8,en;q=0.6',
        'Cache-Control'=>'max-age=0'
    ];

    public function __construct()
    {
    }


    public function createSocket($url){
        $this->socket = @stream_socket_client('tcp://www.baidu.com:80', $errNo, $errStr);
        stream_set_blocking($this->socket, 0);
        $this->errNo = $errNo;
        $this->errStr = $errStr;
    }


    public function setParams($name,$value){
        $this->params[$name] = $value;
    }

    public function createContext(){

    }



    public function setMethod($method){
        $this->method = $method;
    }

    public function setLine(){
        $this->line = $this->method . ' ' . $this->route . ' ' .'HTTP/1.1';
    }

    protected function query(){
        $this->content =  http_build_query($this->params) ?: '';
    }

    protected function setHeader(){

        foreach ($this->headers as $name=>$value){
            $this->header .= $name .':' . $value . PHP_EOL;
        }
        $this->header = rtrim($this->header,PHP_EOL);
    }


    public function setRequest(){
        $this->request = $this->line . PHP_EOL . $this->header . PHP_EOL;
        if($this->content){
            $this->request .= PHP_EOL . $this->content;
        }else{
            $this->request .= PHP_EOL;
        }
    }

    //拆分域名和路径
    protected function slice($url){
        $url = str_replace(['http://','https://'],'',$url . '/');
        $this->scheme =  substr($url,0,strpos($url,'/'));
        $route =  substr($url,strpos($url,'/'));
        if($route === '/' && $route ==='//'){
            $route = '/';
        }
        $this->route = $route;
    }

    /**
     * post curl
     * @param $url
     * @param array $params
     * @param array $headers
     * @param array $options
     */
    public function post($url, $params = [], array $headers = [], array $options = []){

    }




    public function get($url, $params = [])
    {
        $data = '';
        $this->slice($url);
        $this->setMethod('GET');
        $this->setLine();
        $this->setHeader();
        $this->setRequest();
        $this->createSocket($url);
        yield from $this->write($this->request);
        return yield from $this->read($this->size);
    }



}