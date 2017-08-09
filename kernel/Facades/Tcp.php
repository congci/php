<?php
namespace kernel\Facades;

class Tcp extends facade
{

    public static function getInstance(){
        return \kernel\Protocol\Tcp::class;
    }


}