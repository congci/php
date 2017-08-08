<?php

chdir(__DIR__);
define('APPC',__DIR__ . 'app/controller');
//自动加载
require_once __DIR__ . '/autoload.php';

//基本函数
require_once __DIR__ . '/lib/func.php';

//配置模块
require_once __DIR__ . '/Config.php';
//路由模块
require_once __DIR__ . '/Route.php';




$Kernel = new Kernel;
$Kernel->run();
