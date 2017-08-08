<?php
/**
 * info 调度器
 *
 */

class Scheduler
{
    protected $maxTaskId = 0;
    private $taskQueue;
    protected $taskMap = []; // taskId => task
    protected $waitingForRead  = [];
    protected $waitingForWrite = [];

    public  function __construct(){
        $this->taskQueue = new SplQueue();
    }



    public function addTask(Generator $coroutine){
        $tid = ++$this->maxTaskId;
        $task = new Task($tid,$coroutine);
        $this->taskMap[$tid] = $task;
        $this->schedule($task);
        return $tid;
    }

    public function schedule(Task $task){
        $this->taskQueue->enqueue($task);
    }


    public function killTask($tid){
        if (!isset($this->taskMap[$tid])) {
            return false;
        }
        unset($this->taskMap[$tid]);
        foreach ($this->taskQueue as $i => $task){
            if ($task->getTaskId() === $tid) {
                unset($this->taskQueue[$i]);
                break;
            }
        }
        return true;
    }

    public function waitForWrite($socket,Task $task){

        if(isset($this->waitingForWrite[ (int) $socket])){
            $this->waitingForWrite[(int) $socket][1][] = $task;
        }else{
            $this->waitingForWrite[(int) $socket] = [$socket, [$task]];
        }
    }

    public function waitForRead($socket,Task $task){

        if(isset($this->waitingForWrite[ (int) $socket])){
            $this->waitingForRead[(int) $socket][1][] = $task;
        }else{
            $this->waitingForRead[(int) $socket] = [$socket, [$task]];
        }
    }


    public function run(){
        while(1){
            pcntl_signal_dispatch();
            $this->ioPollTask();
            if(!$this->taskQueue->isEmpty()){
                $task = $this->taskQueue->shift();
                $retval = $task->exec();

                if (is_callable($retval)) {
                    $retval($task, $this);   //因为这里没有把任务再次假如到队列中、需要 retval变量代表的函数把任务再次加入
                    continue;
                }

                if($task->isFinished()){
                    unset($this->taskMap[$task->getTaskId()]);
                }else{
                    $this->schedule($task);
                }
            }else{
                sleep(1);
            }


        }



    }

    protected function ioPoll($timeout){
        $rSocks = [];
        foreach ($this->waitingForRead as list($socket)){
            $rSocks[] = $socket;
        }
        $wSocks = [];
        foreach ($this->waitingForWrite as list($socket)){
            $wSocks[] = $socket;
        }

        $eSocks = [];
        if(empty($rSocks) && empty($wSocks)){
            return false;
        }

        if(!stream_select($rSocks,$wSocks,$eSocks,$timeout)){
            return false;
        }

        foreach ($rSocks as $socket){
            list(,$tasks) = $this->waitingForRead[(int) $socket];
            unset($this->waitingForRead[(int) $socket]);
            foreach ($tasks as $task) {
                $this->schedule($task);
            }
        }

        foreach ($wSocks as $socket) {
            list(, $tasks) = $this->waitingForWrite[(int) $socket];
            unset($this->waitingForWrite[(int) $socket]);
            foreach ($tasks as $task) {
                $this->schedule($task);
            }
        }

    }

    protected function ioPollTask() {
        if ($this->taskQueue->isEmpty()) {
            $this->ioPoll(null);
        } else {
            $this->ioPoll(0);
        }
    }




}