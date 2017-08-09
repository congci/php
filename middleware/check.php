<?php
/**
 * info 中间件
 */
namespace middleware;

use Closure;


class Check{






    public function handle($request, Closure $next){

        return yield 1;
//        return next($request);
    }
}