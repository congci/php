<?php

namespace kernel;

class Work
{
    protected $midelware = [];



    /**
     * info 主要执行函数
     * @param $socket
     * @return Generator
     */
    function handle($socket) {
        $data = yield from read($socket,8192);
        //parse request
        $request = new Request();
        $request->parse($data);

        //exec
        try{
            //需要实例化
            $data = yield from (new Pipeline())
                ->send($request)
                ->through($this->midelware)
                ->then((new Route)->dispatch());

            $data = $data === false ? '' : $data;

            $code = 200;
        }catch (\Exception $e){
            $code = 502;
        }
        $response = new Response();
        $sendContent = $response->setResponse($data,$code);
        yield from write($socket,$sendContent);
        fclose($socket);
    }

}