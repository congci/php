<?php
/**
 * info 中间键
 */
namespace middleware;

use Closure;


class Check{






    public function handle($request, Closure $next){

        return 1;
//        return next($request);
    }
}