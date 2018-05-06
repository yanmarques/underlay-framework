<?php

namespace Underlay\Tests\Events;

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
}