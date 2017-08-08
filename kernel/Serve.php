<?php

/***
 * info 创建服务器
 *
 * tcp udp
 *
 */
namespace kernel;
use Exception;


class Serve{
    protected $process = [
        'protocol' => 'tcp',
        'host'     => '127.0.0.1',
        'port'     => '8000',
    ];
    protected $protocol;
    protected $host;
    protected $port;



    public function serverChild($socket) {
        stream_set_blocking($socket, 0);
        while (true) {
            if($clientSocket = yield from accept($socket)){
                yield addTask((new Work)->handle($clientSocket));
            }
        }
    }

    public function createServe($process){
        $process += $this->process;
        $this->protocol = $process['protocol'];
        $this->host = $process['host'];
        $this->port = $process['port'];

        $socket = @stream_socket_server($this->protocol . '://' . $this->host . ':'  . $this->port, $errNo, $errStr);
        if (!$socket) throw new Exception($errStr, $errNo);
        return $socket;
    }




}


