<?php

/**
 * info kernel
 * require pcntl、pcntl
 *
 */

use kernel\Scheduler;
use kernel\Config;
use kernel\Route;
use kernel\Server;


class Kernel{

    protected $alias;
    protected $starTtime;
    protected $masterPid;
    protected $clildPids;
    protected $logFile = 'app.log';
    protected $pidFile = 'app.pid';
    protected $route = null;
    protected $condig = null;
    protected $configData  = [];
    protected $routeData   = [];
    protected $event = \kernel\Event\Select::class;
    protected $serverName;




    /**
     * run
     */
    public function run(){
        $this->checkEnv();
//        $this->changeStd();
        $this->parseCommond();
        $this->registerTicks();
        $this->initServes();
        $this->setMasterPid();
        $this->masterLoop();

    }

    /**
     * 简单设置主进程
     */
    protected function masterLoop(){
        while(1){
            sleep(1);
            pcntl_signal_dispatch();
        }
    }





    /**
     * info create pid file
     */
    protected function createPidFile(){
        if($this->checkPidFile()){
            exit('process has exists');
        }
        touch($this->pidFile);
    }


    protected function unclock(){
        unlink($this->pidFile);
    }

    protected function checkPidFile(){
        return file_exists($this->pidFile);
    }

    protected function readFilepid(){
        return file_get_contents($this->pidFile);
    }




    /**
     * daemon process
     */
    public function daemon(){
        umask(0);
        $pid = pcntl_fork();
        if(-1 == $pid)
        {
            exit("Can not fork");
        }
        elseif($pid > 0)
        {
            exit(0);
        }
        if(-1 == posix_setsid())
        {
            exit("Setsid fail");
        }
        $pid2 = pcntl_fork();

        if(-1 == $pid2)
        {
            exit("Can not fork");
        }
        elseif($pid2 != 0)
        {
            exit(0);

        }

    }

    protected function setMasterPid(){
        $this->masterPid = posix_getpid();
        if(false === file_put_contents($this->pidFile,$this->masterPid)){

        }
    }

    /**
     * info change STD
     */
    protected function changeStd(){
        global $STDERR,$STDOUT;
        fclose(STDERR);
        fclose(STDOUT);
        if($fd = fopen('','a')){
            $STDERR = $fd;
        }
        if($fd = fopen('','a')){
            $STDOUT = $fd;
        }
    }
    /**
     * info check env
     */
    protected function checkEnv(){
        $this->checkCli();
        $this->checkUid();
    }

    /**
     * info check cli
     */
    public function checkCli(){
        if('cli' !== php_sapi_name()){
            exit('must cli');
        }
    }

    protected function checkUid(){

    }



    protected function registerTicks()
    {
        // stop
        pcntl_signal(SIGINT, array($this, 'signalHandler'), false);
        // reload
        pcntl_signal(SIGUSR1, array($this, 'signalHandler'), false);
        // status
        pcntl_signal(SIGUSR2, array($this, 'signalHandler'), false);
        // ignore
        pcntl_signal(SIGPIPE, SIG_IGN, false);
    }


    public function signalHandler($signo){
        switch ($signo){
            case SIGINT :
                $this->signalClose();
                break;
            case SIGUSR1 :
                $this->reload();
                break;
            case SIGUSR2 :
                $this->status();
                break;
        }

    }

    protected function getUid(){
        return posix_getuid();
    }


    protected function getPid(){
        return posix_getpid();
    }

    /**
     * info start serves
     */
    protected function initServes(){
        $servers = config_item('servers');
        foreach ($servers as $name => $server){
            if(!empty($server)){
                $this->initserve($name,$server);
            }
        }
    }

    /**
     * info start single serve
     * @param Serve $serve
     * @param $socket
     */
    protected function initserve($name,$server){
        $serve     = new Server;
        $socket    = $serve->createServe($server);

        $num = $server['num'] ?? 1;
        if($num <= 0){
            return false;
        }
        //create process
        while($num--){
            $childPid = pcntl_fork();
            if($childPid < 0){

            } elseif($childPid){
                $this->clildPids[$name] = $childPid;
            } elseif($childPid == 0){
                //child
                $scheduler = new Scheduler;
                $scheduler->addTask($serve->serverChild($socket));
                $event = new $this->event($scheduler);
                $scheduler->run($event);
            }
        }

    }


    /**
     * info get process status
     */
    protected function status(){

    }


    /**
     * info reload
     *
     * php XXX.php reload .../all
     *
     */
    protected function reload(){
        if(!$this->checkPidFile()){
            exit('there are no process');
        }

        //每个pid都重启

    }



    /**
     * info start
     *
     * php XXX.php start .../all
     */
    protected function start(){
        if($this->checkPidFile()){
            exit('process had exists');
        }

        if($this->serverName !== 'all'){


        }
        return;
    }

    /**
     * info close
     *
     * php XXX.php close .../all
     *
     */
    protected function close(){
        $masterPid = $this->readFilepid();
        posix_kill($masterPid,SIGINT);
        exit;
    }

    //执行信号处理、主进程是发送信号、子进程是退出
    public function signalClose(){
        $masterPid = $this->readFilepid();
        if($masterPid != posix_getpid()){
            exit;
        }
        foreach ($this->clildPids as $pid){
            posix_kill($pid,SIGINT);
        }
        //回收僵死进程（子进程结束、父进程无法收到结束信息）
        pcntl_wait($status);
        unlink($this->pidFile);
        exit;
    }

    /**
     * info 单一调试
     */
    protected function debug(){
        $servers = config_item('servers');
        $server = $this->serverName == 'all' ? array_shift($servers) :$servers[$this->serverName];
        if(empty($server)){
            exit();
        }
        $serve     = new Server;
        $socket    = $serve->createServe($server);
        $scheduler = new Scheduler;
        $scheduler->addTask($serve->serverChild($socket));
        $event = new $this->event($scheduler);
        $scheduler->run($event);
    }

    //单一开启
    protected function startServerOne(){
        $servers = config_item('servers');
        $server = $servers[$this->serverName] ?? 0;
        if(empty($server)){
            exit();
        }
        $this->initserve($this->serverName,$server);
        exit();
    }





    /**
     * info parse commond
     */
    protected function parseCommond(){
        global $argv;
        $this->serverName = $argv[2] ?? 'all';
        $argv[1] = $argv[1] ?? '';
        switch(strtoupper($argv[1])){
            case '' :
            case 'DAEMON':
                $this->daemon();
                break;
            case 'START'  :
                $this->start();
                break;
            case 'STOP'  :
            case 'CLOSE' :
                $this->close();
                break;
            case 'RESTART':
            case 'RELOAD' :
                $this->reload();
                break;
            case 'STATUS' :
                $this->status();
                break;
            case 'DEBUG' :
                $this->debug();
                break;
            default :
                break;
        }

    }


    public static function shutdownHandler(){
        //写入到日志
        $ERROR = error_get_last();
        switch ($ERROR['type']){


        }




    }

    public static function exceptionHandler(Exception $e){
        //将异常写入到日志

    }

    public static function errorHandler($code, $description, $file = null, $line = null, $context = null){
        //错误处理\写入日志

    }



    public function __construct()
    {
        //加载配置和路由
        $this->loadConfig();
        //设置别名
        $this->setClassAlias();
        //路由
        $this->loadRoute();
        //选择事件方式
        $this->chooseEvent();
        //异常处理
        set_exception_handler(['Kernel','exceptionHandler']);
        //错误处理
        set_error_handler(['Kernel','errorHandler']);
        //关闭处理
        register_shutdown_function(['Kernel','shutdownHandler']);

    }

    /**
     * info set class alias
     */
    public function setClassAlias(){
        $alias = array_flip(config_item('alias'));
        if(!$alias){
            return false;
        }
        foreach ($alias as $key=>$v){
            class_alias($key,$v);
        }
    }


    /**
     * require config
     */
    protected function loadConfig(){
        $this->config = new Config();
        $this->config->requires();
//        $this->configData = $this->config->getAll();
    }

    /**
     * info require route
     */
    protected function loadRoute(){
        $this->route = new Route();
        $this->route->requires();
//        $this->routeData = $this->route->getAll();
    }

    protected function chooseEvent(){
        $this->event = config_item('serve')['event'] ?: $this->event;
    }





}


