<?php
/**
 * info PHP自己的stream_select
 */
namespace kernel\Event;

use kernel\Scheduler;
use kernel\Task;


class Select implements EventInterface
{
    protected $_Read  = [];
    protected $_Write = [];
    protected $scheduler;

    public function __construct(Scheduler $scheduler)
    {
        $this->scheduler = $scheduler;
    }


    /**
     *
     */
    public function add($fd,$flag,$task){
        switch ($flag){
            case self::EV_READ :
                $this->waitForRead($fd,$task);
                break;
            case self::EV_WRITE :
                $this->waitForRead($fd,$task);
                break;
        }


    }

    /**
     * fd
     */
    public function del($fd,$flag){

    }



    public function waitForWrite($socket,Task $task){
        $key = (int)$socket;
        if(isset($this->_Write[$key])){
            $this->_Write[$key][1][] = $task;
        }else{
            $this->_Write[$key] = [$socket, [$task]];
        }
    }

    public function waitForRead($socket,Task $task){

        $key = (int)$socket;
        if(isset($this->_Read[$key])){
            $this->_Read[$key][1][] = $task;
        }else{
            $this->_Read[$key] = [$socket, [$task]];
        }
    }

    protected function ioPoll($timeout){
        $rSocks = [];
        foreach ($this->_Read as list($socket)){
            $rSocks[] = $socket;
        }
        $wSocks = [];
        foreach ($this->_Write as list($socket)){
            $wSocks[] = $socket;
        }

        $eSocks = [];


        if(empty($rSocks) && empty($wSocks)){
            return false;
        }
        pcntl_signal_dispatch();
        if(!stream_select($rSocks,$wSocks,$eSocks,$timeout)){
            return false;
        }

        foreach ($rSocks as $socket){
            $key = (int)$socket;
            list(,$tasks) = $this->_Read[$key];
            unset($this->_Read[$key]);
            foreach ($tasks as $task) {
                $this->scheduler->enqueue($task);
            }
        }

        foreach ($wSocks as $socket) {
            $key = (int)$socket;
            list(, $tasks) = $this->_Write[$key];
            unset($this->_Write[$key]);
            foreach ($tasks as $task) {
                $this->scheduler->enqueue($task);
            }
        }

    }


    public function loop(){
        if ($this->scheduler->taskQueue->isEmpty()) {
            $this->ioPoll(null);
        } else {
            $this->ioPoll(0);
        }


    }



}