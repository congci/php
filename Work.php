<?php


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
            $data = (new Pipeline())
                ->send($request)
                ->through($this->midelware)
                ->then(Route::getInstance()->dispatch());


            $code = 200;
        }catch (\Exception $e){
            $code = 502;
        }
        $response = new Response();
        //set response
        $sendContent = $response->setResponse($data,$code);
        yield from write($socket,$sendContent);
        fclose($socket);
    }

}