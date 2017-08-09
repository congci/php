<?php
namespace kernel\Protocol;


class Socket{
    protected $socket;

    public function __construct() {

    }

    public function setSocket($socket){
        $this->socket = $socket;
    }

    public function accept() {
        yield waitForRead($this->socket);
        return stream_socket_accept($this->socket, 0);
    }

    public function read($size) {
        yield waitForRead($this->socket);
        return fread($this->socket, $size);
    }

    public function write($string) {
        yield waitForWrite($this->socket);
        fwrite($this->socket, $string);
    }

    public function close() {
        @fclose($this->socket);
    }

    public function get($name){
        return $this->$name;
    }

    public function __get($name){
        if(property_exists($this, $name)){
            return $this->$name;
        }
    }
}