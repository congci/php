<?php
/**
 * info request
 *
 */
namespace kernel;

class Request
{

    public $params;
    public $requestMethod;
    public $path;
    protected $line;
    protected $header;
    protected $headerArr;
    protected $content;
    protected $procotol;
    public $class;
    public $method;


    public function __construct()
    {
    }

    /**
     * info 解析
     */
    public function parse($requestString){
        $lnum = strpos($requestString,PHP_EOL);
        $this->line = substr($requestString,0,$lnum);
        if($cnum = strpos($requestString,PHP_EOL.PHP_EOL)){
            $this->content = substr($requestString,$cnum);
            $this->header  = substr($requestString,$lnum,$cnum - ($lnum+1));
        }else{
            $this->header  = substr($requestString,$lnum+1);
        }
        //line
        $this->parseLine();
        //headr
        $this->parseHeader();
        //content
        if($cnum){
            $this->parseContent();
        }
    }

    protected function parseLine(){
        list($this->requestMethod,$this->path,$this->procotol) = explode(' ',$this->line);
    }

    protected function parseContent(){
        if('GET' == $this->requestMethod){
            parse_str($this->content,$this->params);
        }
    }

    protected function parseHeader(){
        $headerArr = explode(PHP_EOL,trim($this->header));
        foreach ($headerArr as $value){
            list($name,$v) = explode(':',$value,2);
            $this->headerArr[$name] = $v;
        }
    }

    public function parseRpc(){

    }



}