<?php
/**
 * info 调度器
 *
 */

namespace kernel;

use Generator;
use SplQueue;


class Scheduler
{
    protected $maxTaskId = 0;
    public $taskQueue;
    protected $taskMap = []; // taskId => task

    public  function __construct(){
        $this->taskQueue = new SplQueue();
    }



    public function addTask(Generator $coroutine){
        $tid = ++$this->maxTaskId;
        $task = new Task($tid,$coroutine);
        $this->taskMap[$tid] = $task;
        $this->enqueue($task);
        return $tid;
    }

    public function enqueue(Task $task){
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




    public function run($event){
        while(1){
            pcntl_signal_dispatch();
            $event->loop();
            if(!$this->taskQueue->isEmpty()){
                $task = $this->taskQueue->shift();
                $retval = $task->exec();

                if (is_callable($retval)) {
                    $retval($task, $this,$event);   //因为这里没有把任务再次假如到队列中、需要 retval变量代表的函数把任务再次加入
                    continue;
                }

                if($task->isFinished()){
                    unset($this->taskMap[$task->getTaskId()]);
                }else{
                    $this->enqueue($task);
                }
            }else{
                sleep(1);
            }


        }



    }







}