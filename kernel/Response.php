<?php
/**
 * info response
 */
namespace kernel;

class Response
{
    protected $line;
    protected $header;
    protected $content;
    protected $method;
    protected $status;
    protected $statusText;

    protected $headerArr = [
        'Content-Type'=>'text/html;charset=utf-8;',
        'Connection' =>'close'
    ];
    protected static  $statusTexts = array(
        '100' => 'Continue',
        '101' => 'Switching Protocols',
        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',
        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '306' => '(Unused)',
        '307' => 'Temporary Redirect',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '407' => 'Proxy Authentication Required',
        '408' => 'Request Timeout',
        '409' => 'Conflict',
        '410' => 'Gone',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request-URI Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range Not Satisfiable',
        '417' => 'Expectation Failed',
        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
        '505' => 'HTTP Version Not Supported',
    );

    public $sendContent;

    protected function setLine($code){
        $this->line = 'HTTP/1.1 ' . $code . ' ' . self::$statusTexts[$code];
    }

    protected function setContent($data){
        $this->content = PHP_EOL . $data;
    }

    protected function setHeader($data,$headerArr){
        $strlen = strlen($data);
        $this->header = '';
        $headerArr += $this->headerArr;
        $headrArr['Content-Length'] = $strlen;
        foreach ($headerArr as $key=>$value){
            $this->header .= $key . ':' . $value . PHP_EOL;
        }
    }

    protected function setCookie(){}

    public function setResponse($data,$code){
        $this->setLine($code);
        $this->setHeader($data,$headerArr=[]);
        $this->setContent($data);
        $content  = $this->line . PHP_EOL . $this->header . $this->content;
        return $content;

    }

    protected function parseLine(){
        list($this->method,$this->status,$this->statusText) = explode(' ',$this->line);
    }


    //解析的时候要考虑不同环境的换行
    public function parse($string){
        $EOL = '';
        if(strpos($string,"\r\n")){
            $EOL = "\r\n";
        }elseif (strpos($string,"\r")){
            $EOL = '\r';
        }elseif(strpos($string,"\n")){
            $EOL = '\n';
        }
        $lnum = strpos($string,$EOL);
        $this->line = substr($string,0,$lnum);
        if($cnum = strpos($string,$EOL.$EOL)){
            $this->content = substr($string,$cnum+2);
            $this->header  = substr($string,$lnum, $cnum - ($lnum+1));
        }else{
            $this->content = '';
            $this->header  = substr($string,$lnum+1);
        }
    }



    protected function parseHeader(){
        $headerArr = explode(PHP_EOL,trim($this->header));
        foreach ($headerArr as $value){
            list($name,$v) = explode(':',$value,2);
            $this->headerArr[$name] = $v;
        }
    }


    public function __get($name)
    {
        if(property_exists($this,$name)){
            return $this->$name;
        }
    }


}