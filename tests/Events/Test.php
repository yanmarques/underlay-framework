<?php

namespace Underlay\Tests\Events;

use Closure;

class Test
{
    /**
     * Just change the boolean to true if is false and false if is true. 
     * 
     * @param  bool $tester
     * @return bool
     */
    public function not(bool $tester)
    {
        return ! $tester;
    }

    /**
     * Call an anonymous function.
     * 
     * @param  mixed $arg
     * @param  Closure $callback
     * @return void
     */
    public function call($arg, Closure $callback)
    {
        call_user_func($callback, $arg);
    }
}