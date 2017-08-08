<?php

class autoload{

    public static function loadClass($classname){
        if(strpos($classname,'\\')){
            $classname = str_replace('\\','/',$classname);
        }
        require_once $classname . ".php";
    }

}
spl_autoload_register(array('autoload','loadClass'), true);