<?php
/**
 * info 中间键
 */
namespace middleware;


class Check{






    public function handle($request, \Closure $next){

        return next($request);
    }
}