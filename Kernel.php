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
    protected $pidMaps = [];
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
//        $this->createPidFile();
//        $this->changeStd();
        $this->parseCommond();
        $this->registerTicks();
        $this->initServes();
//        $this->setMasterPid();
    }





    /**
     * info create pid file
     */
    protected function createPidFile(){
        if($this->checkPidFile()){
            die('process has exists');
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
        return file_get_content($this->pidFile);
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
            exit();

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
            die('must cli');
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


    protected function signalHandler($signo){
        switch ($signo){
            case SIGINT :
                $this->close();
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
        $pid = pcntl_fork();
        if($pid < 0){
        }
        if($pid){
            //master
            $this->clildPids[$pid] = [];
            $this->clildPids[$pid][0] = $pid;

            $this->pidMaps[$name] = $pid;
        }else{
            //slave
            $num = $server['num'] ?? 0;
            if($num <= 0){
                return false;
            }
            //create process
            while($num--){
                $childPid = pcntl_fork();
                if($childPid < 0){

                } elseif($childPid){
                    $this->clildPids[$pid][] = $childPid;
                } elseif($childPid == 0){
                    //child
                    $scheduler = new Scheduler;
                    $scheduler->addTask($serve->serverChild($socket));
                    $event = new $this->event($scheduler);
                    $scheduler->run($event);
                }
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
            die('there are no process');
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
            die('process had exists');
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
        if($masterPid != posix_getpid()){
            die();
        }
        if($this->serverName == 'all'){
            $pids = [];
            foreach ($this->clildPids as $value){
                $pids += $value;
            }
        }else{
            $pids = $this->clildPids[$this->pidMaps[$this->serverName]];
        }
        foreach ($pids as $pid){
            posix_kill($pid,SIGINT);
        }
    }

    /**
     * info 单一调试
     */
    protected function debug(){
        $servers = config_item('servers');
        $server = $this->serverName == 'all' ? array_shift($servers) :$servers[$this->serverName];
        if(empty($server)){
            die();
        }
        $serve     = new Server;
        $socket    = $serve->createServe($server);
        $scheduler = new Scheduler;
        $scheduler->addTask($serve->serverChild($socket));
        $event = new $this->event($scheduler);
        $scheduler->run($event);
    }

    protected function startServerOne(){
        $servers = config_item('servers');
        $server = $servers[$this->serverName] ?? 0;
        if(empty($server)){
            die();
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
        if(count($argv) == 1){
            $this->debug();
        }

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
        echo 1;

    }

    public static function exceptionHandler(){

    }

    public static function errorHandler(){

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
//        //异常处理
//        set_exception_handler([$this,'exceptionHandler']);
//        //错误处理
//        set_error_handler([$this,'errorHandler']);
//
        register_shutdown_function(['Kernel','shutdownHandler']);


        //
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


