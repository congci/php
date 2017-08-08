<?php




class Pipeline
{
    protected $passable;
    protected $pipes = [];
    protected $method = 'handle';


    public function __construct()
    {

    }

    public function send($passable)
    {
        $this->passable = $passable;

        return $this;
    }


    public function through($pipes)
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();
        return $this;
    }

    protected function prepareDestination(Closure $destination)
    {
        return function ($passable) use ($destination) {
            return $destination($passable);
        };
    }

    /**
     * 闭包串闭包、然后从尾部执行、依次往上、其实就是个炮仗
     * 如果handle中没有next()、那么就直接中断了、
     */
    public function then(Closure $destination = null)
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes), $this->carry(),$this->prepareDestination($destination)
        );
        return $pipeline($this->passable);
    }

    /**
     * @return \Closure
     */
    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                //如果注册的中间件是个回调、则直接执行
                if ($pipe instanceof Closure) {
                    return $pipe($passable, $stack);
                }
                return $pipe->{$this->method}();
            };
        };
    }
}
