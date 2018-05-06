<?php

namespace Underlay\Tests\Events;

use PHPUnit\Framework\TestCase;
use Underlay\Events\Dispatcher;

class DispatcherTest extends TestCase
{
    /**
      * The dispatcher instance.
      * 
      * @var Underlay\Events\Dispatcher
      */
    public $dispatcher;

    public function setUp()
    {
        parent::setUp();
        $this->dispatcher = new Dispatcher;
    }

    /**
     * Pass if callback changed the test to 1.
     * 
     * @return void
     */
    public function testDispatchActionWithValidCallable()
    {
        $test = 0;

        // Increase the test number. Should be 1.
        $test = $this->dispatcher->dispatch(function ($number) {
            return $number + 1;
        }, [$test]);

        $this->assertTrue($test == 1);
    }

    /**
     * Pass if callback changed the test to true.
     * 
     * @return void
     */
    public function testDispatchActionWithValidString()
    {
        $test = false;

        // Increase the test number. Should be true.
        $test = $this->dispatcher->dispatch('Underlay\Tests\Events\Test@not', [$test]);

        $this->assertTrue($test);
    }
    
    /**
     * Pass if throw and TypError.
     * 
     * @expectedException \TypeError
     */
    public function testDispatchActionWithInValidType()
    {
        $this->dispatcher->dispatch([1,2]);
    }

    /**
     * Pass if throw and InvalidArgumentException.
     * 
     * @expectedException \InvalidArgumentException
     */
    public function testDispatchActionWithInValidClass()
    {
        $this->dispatcher->dispatch('FooClass@any');
    }
}