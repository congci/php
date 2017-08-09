<?php

function count_to_ten() {
    $data =  yield from nine_ten();
//    var_dump($data);

}

$a = 0;
$b = count_to_ten();






function nine_ten() {
    yield 9;
    yield 3;
    return 4;//c();
}


//可以忽略普通函数\只要最上级是yield是ok

function c(){
return d();
}

function d(){
//     yield from h();
    return yield 1;


}

function e(){
    $a = yield from c();

    echo $a;

}

function h(){
    yield 0;
    yield 1;
}

$b = e();
while(1){
    if($b->valid()){
        if(!$a){
            echo $b->current();
            $a = true;
        }else{
            echo $b->send(1);
        }
    }else{
        break;
    }
}





