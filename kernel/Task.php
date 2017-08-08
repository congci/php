<?php
/**
 * info 任务
 */
namespace kernel;

use Generator;

class Task{

    private $taskid;
    private $coroutine;
    private $beforeFirstYield = true;
    private $sendValue = null;


    public function __construct($taskid,Generator $coroutine)
    {
        $this->taskid    = $taskid;
        $this->coroutine = $coroutine;
    }

    public function getTaskId(){
        return $this->taskid;
    }

    public function setSendValue($sendValue) {
        $this->sendValue = $sendValue;
    }

    public function exec(){
        if($this->beforeFirstYield){
            $this->beforeFirstYield = false;
            $res =  $this->coroutine->current();
            $this->sendValue = $res;
            return $res;
        }else{
            $retval =  $this->coroutine->send($this->sendValue);
            $this->sendValue = $retval;
            return $retval;
        }
    }

    public function isFinished() {
        return !$this->coroutine->valid();
    }


}