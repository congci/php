<?php

namespace kernel\Facades;


class Event
{
    public static function getInstance(){
        return \kernel\Event\Select::class;
    }





}