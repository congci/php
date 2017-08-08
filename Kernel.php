<?php

/**
 * info
 * require pcntl、pcntl
 *
 * //内核、兼具对象容器
 */


class Kernel{

    protected $alias;
    protected $starTtime;
    protected $masterPid;
    protected $clildPids;
    protected $logFile = 'defaultlog.log';
    protected $pidFile = 'yield.pid';
    protected $pidMaps = [];
    protected $routeInstance = null;
    protected $condigInstance = null;
    protected $configData  = [];
    protected $routeData   = [];




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
        pcntl_signal(SIGKILL, array($this, 'signalHandler'), false);
        // reload
        pcntl_signal(SIGUSR1, array($this, 'signalHandler'), false);
        // status
        pcntl_signal(SIGUSR2, array($this, 'signalHandler'), false);
        // ignore
        pcntl_signal(SIGPIPE, SIG_IGN, false);
    }


    protected function signalHandler($signo){
        switch ($signo){
            case SIGKILL :
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
        $process = config_item('process');
        foreach ($process as $k => $v){
            $this->initserve($k,$v);
        }
    }

    /**
     * info start single serve
     * @param Serve $serve
     * @param $socket
     */
    protected function initserve($alias,$process){
        $serve     = new Serve;
        $socket    = $serve->createServe($process);
        $pid = pcntl_fork();
        if($pid < 0){
        }
        if($pid){
            //master
            $this->clildPids[$pid] = [];
            $this->clildPids[$pid][0] = $pid;

            $this->pidMaps[$alias] = $pid;
        }else{
            //slave
            $num = $process['num'] ?? 0;
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
                    $scheduler->run();
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
     * require config
     */
    protected function loadConfig(){
        Config::getInstance()->requires();
        $this->configData = Config::getInstance()->getAll();
    }

    /**
     * info require route
     */
    protected function loadRoute(){
        Route::getInstance()->requires();
        $this->routeData = Route::getInstance()->getAll();
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
        if($this->alias == 'all'){
            $pids = [];
            foreach ($this->clildPids as $value){
                $pids += $value;
            }
        }else{
            $pids = $this->clildPids[$this->pidMaps[$this->alias]];
        }
        foreach ($pids as $pid){
            posix_kill($pid,SIGKILL);
        }
    }

    /**
     * info 单一调试
     */
    protected function debug(){
        $alias = $this->alias == 'all' ? 'tcp' :$this->alias;
        $process = config_item('process.' . $alias);
        $serve     = new Serve;
        $socket    = $serve->createServe($process);
        $scheduler = new Scheduler;
        $scheduler->addTask($serve->serverChild($socket));
        $scheduler->run();
    }





    /**
     * info parse commond
     */
    protected function parseCommond(){
        global $argv;
        $this->alias = $argv[2] ?? 'all';
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

    public function __construct()
    {
        $this->loadConfig();
        $this->loadRoute();

    }







}


