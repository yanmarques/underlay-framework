<?php

namespace Underlay\Tests\Events;

use PHPUnit\Framework\TestCase;
use Underlay\Events\EventManager;
use Closure;

class EventManagerTest extends TestCase
{
    /**
     * Pass if the parameter fired is the same received on listener.
     * 
     * @return void
     */
    public function testEventWithClosure()
    {
        $eventManager = new EventManager;
        $secret = '1234';
        $eventManager->listen('secret', function ($newSecret) use ($secret) {
            $this->assertEquals($newSecret, $secret);
        });
        $eventManager->fire('secret', $secret);
    }

    /**
     * Pass if the parameter fired is the same received on listener.
     * 
     * @return void
     */
    public function testFireEventWithClass()
    {
        $eventManager = new EventManager;
        $secret = '1234';
        $eventManager->listen('secret', 'Underlay\Tests\Events\Test@call', function (
                $receivedSecret
            )
            use (
                $secret
            ) {
                $this->assertEquals($secret, $receivedSecret);
        });
        $eventManager->fire('secret', $secret);
    }

    /**
     * Pass if the event is received on the internal fired event.
     * 
     * @return void
     */
    public function testFireEventWithInternalFiredEvent()
    {
        $eventManager = new EventManager;
        $event = 'foor.bar';
        $eventManager->listen(EventManager::FIRED, function ($newEvent) use ($event) {
            $this->assertEquals($newEvent, $event);
        });
        $eventManager->fire($event);
    }

    /**
     * Pass if the event receive the exception.
     * 
     * @return void
     */
    public function testFireEventWithInternalOnException()
    {
        $eventManager = new EventManager;
        $eventManager->listen(EventManager::ON_EXCEPTION, function ($ex) {
                $this->assertTrue($ex instanceof \Exception);
        });
        $eventManager->listen('thrown', function () {
            throw new \Exception('');
        });
        $eventManager->fire('thrown');
    }
}