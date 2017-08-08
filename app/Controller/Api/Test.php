<?php

namespace app\Controller\Api;



class Test
{
    public function index(){
        return 'hello word';
    }


    /**
     *  模拟代码
     *  未实现
     * @param Request $request
     * @return mixed
     */
    public function getData(Request $request){
        $user_id = $request->only(['user_id']);
        $mysql = new mysql();
        $data = yield from $mysql::select('select * from user where id=?',[$user_id]);
        return $data;
    }
}

