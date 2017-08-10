<?php

namespace kernel\Protocol;

use kernel\Response;

class Tcp extends Socket
{
    protected $socket;
    protected $errNo;
    protected $errStr;
    protected $method;
    protected $scheme;
    protected $port;
    protected $line;
    protected $header;
    protected $path;
    protected $request;
    protected $size = 65535;
    protected $uri;
    protected $params = [


    ];
    protected $query;


    protected $schemeMap = [
        'tcp'  => 'tcp',
        'http' => 'tcp',
    ];
    protected $headers = [
        'Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'Connection'=>'close',
        'Accept-Language'=>'zh-CN,zh;q=0.8,en;q=0.6',
        'Host' => 'www.w3school.com.cn'
    ];

    public function __construct()
    {
    }


    public function createSocket(){
        $this->socket = @stream_socket_client($this->uri, $errNo, $errStr);
        stream_set_blocking($this->socket, 0);
        $this->errNo = $errNo;
        $this->errStr = $errStr;
    }



    public function createContext(){

    }



    public function setMethod($method){
        $this->method = $method;
    }

    public function setLine(){
        $this->line = $this->method . ' ' . $this->path . ' ' .'HTTP/1.1';
    }


    protected function setHeader(){

        foreach ($this->headers as $name=>$value){
            $this->header .= $name .':' . $value . PHP_EOL;
        }
        $this->header = trim($this->header);
    }


    public function setRequest(){
        $this->request = $this->line . PHP_EOL . $this->header . PHP_EOL;
        if($this->query){
            $this->request .= PHP_EOL . $this->query;
        }else{
            $this->request .= PHP_EOL;
        }
    }

    //拆分域名和路径
    protected function parseUrl($url){
       $urlArr = parse_url($url);
       $this->scheme = $this->schemeMap[$urlArr['scheme']];
       $this->host   = $urlArr['host'];
       $this->port   = $urlArr['port'] ?? 80;
       $this->path   = $urlArr['path'] ?? '/';
       $this->uri = $this->scheme . '://' . $this->host . ':' . $this->port;
       $this->query = $urlArr['query'] ?? '';
    }

    /**
     * post curl
     * @param $url
     * @param array $params
     * @param array $headers
     * @param array $options
     */
    public function post($url, $params = []){

    }




    public function get($url,$params = [])
    {
        $this->parseUrl($url);
        $this->setMethod('GET');
        $this->setLine();
        $this->setHeader();
        $this->setRequest();
        $this->createSocket();
        yield from $this->write($this->request);
        $data = yield from $this->read($this->size);
        $res = new Response();
        $res->parse($data);
        return $res->content;
    }


}