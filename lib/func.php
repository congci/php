<?php
use kernel\Task;
use kernel\Config;
use kernel\Scheduler;
use kernel\Event\Select;

function getTaskId() {
    return function(Task $task, Scheduler $scheduler) {
        $task->getTaskId();
        $scheduler->enqueue($task);
    };
}

function addTask($coroutine){
    return function(Task $task, Scheduler $scheduler) use ($coroutine){
        $scheduler->addTask($coroutine);
        //重新把这个任务加如到队列中、其实可以放到调度器里面、但是为了灵活、放到此处
        $scheduler->enqueue($task);
    };
}

function killTask($tid){
    return function(Task $task, Scheduler $scheduler) use ($tid) {
        $scheduler->killTask($tid);
        $scheduler->enqueue($task);
    };
}

function waitForRead($socket) {
    return function(Task $task, Scheduler $scheduler,Select $loop) use ($socket) {
        $loop->waitForRead($socket, $task);
    };
}

function waitForWrite($socket) {
    return function(Task $task, Scheduler $scheduler,Select $loop) use ($socket) {
        $loop->waitForWrite($socket, $task);
    };
}



if(!function_exists('config_item')){
    function config_item($name = null){
        if(!$name){
            return (new Config)->getAll();
        }
        static $config;
        if(isset($config[$name])){
            return $config[$name];
        }
        return (new Config)->get($name);
    }
}


function wirte($path, $data, $mode = 'a+')
{
    if ($h = fopen($path, $mode)) {
        flock($h, LOCK_EX | LOCK_NB);
        fwrite($h, $data . PHP_EOL);
        flock($h, LOCK_EX | LOCK_NB);
    } else {
        return false;
    }
    fclose($h);
}



function timeFormat($time_num)
{
    $hour = floor($time_num / 3600);
    $minute = floor(($time_num - $hour * 3600) / 60);
    $second = $time_num - $hour * 3600 - $minute * 60;
    if($hour < 10 && $hour != 0){
        $hour = '0'.$hour;
    };
    if($minute < 10){
        $minute = '0' . $minute;
    };
    if($second < 10){
        $second = '0' . $second;
    }

    if($hour){
        return $hour . ':' . $minute . ':' . $second;
    }else{
        return $minute . ':' . $second;
    }

}

function accept($socket) {
    yield waitForRead($socket);
    return stream_socket_accept($socket, 0);
}

function read($socket,$size) {
    yield waitForRead($socket);
    return fread($socket, $size);
}
function write($socket,$string) {
    yield waitForWrite($socket);
    fwrite($socket, $string);
}
function close($socket) {
    @fclose($socket);
}


