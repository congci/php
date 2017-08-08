<?php
class TcpConnection extends SocketInstance
{
    protected $socket;
    protected $errNo;
    protected $errStr;
    protected $method;
    protected $scheme;
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


    public function createSocket(){
        $this->socket = stream_socket_client('tcp://127.0.0.1:8000', $errNo, $errStr);
        stream_set_blocking($this->socket, 0);
        $this->errNo = $errNo;
        $this->errStr = $errStr;
    }


    public function curlSetopt($name,$value){
        $this->params[$name] = $value;
    }

    public function createContext(){

    }

    protected function setHeader(){
        $this->header = '';
        foreach ($this->headers as $name=>$value){
            $this->header .= $name .':' . $value . '\r';
        }
        $this->header = rtrim($this->header,'\r');
    }

    public function setMethod($method){
        $this->method = $method;

    }

    public function setRequest(){
        $this->request =<<<REQ
        $this->method $this->route HTTP/1.1\r
        $this->header\r\r
        $this->content\r
REQ;

    }

    /**
     * post curl
     * @param $url
     * @param array $params
     * @param array $headers
     * @param array $options
     */
    public function curl_post($url, $params = [], array $headers = [], array $options = []){

    }
    public function query(){
        $this->content =  http_build_query($this->params) ?: '';
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

    public function curl_get($url, $params = [])
    {
        $data = '';
        $fp = stream_socket_client("tcp://127.0.0.1:8000", $errno, $errstr, 30);
        if (!$fp) {
            echo "$errstr ($errno)<br />\n";
        } else {
            fwrite($fp, "GET / HTTP/1.0\r\nHost: www.example.com\r\nAccept: */*\r\n\r\n");
            while (!feof($fp)) {
                $data .= fgets($fp, 1024);
            }
            fclose($fp);
        }
        return $data;
    }



}