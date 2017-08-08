<?php
class mysql{

    public static function select($sql,$params){
//        strstr($sql,'?',$params);
        $socket = stream_socket_client();
        yield from waitForWrite($socket);
        self::command($sql);
        yield from waitForRead();
        return self::parse();
    }


}