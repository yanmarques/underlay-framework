<?php

namespace Underlay\Events;

use Exception;
use InvalidArgument;

class EventManager
{
    /**
     * Called when an listener throws an exception.
     *
     * @var string
     */
    const ON_EXCEPTION = 'on_exception';

    /**
     * Called when an event had been fired.
     *
     * @var string
     */
    const FIRED = 'fired';

    /**
     * Called when and event had been fired.
     *
     * @var string
     */
    const TERMINATED = 'terminated';

    /**
     * List with all functions listeners.
     *
     * @var array
     */
    protected $listeners = [];

    /**
     * List with all internal events.
     *
     * @var array
     */
    protected $internalEvents = [];

    /**
     * List with all events been fired on manager.
     *
     * @var array
     */
    protected $terminatedEvents = [];

    /**
     * The dispachable instance to dispatch event listeners.
     *
     * @var Underlay\Contracts\Dispatchable
     */
    protected $dispatcher;

    /**
     * Class constructor.
     *
     * @param  Underlay\Contracts\Dispatchable $dispatcher
     * @return void
     */
    public function __construct(Dispatchable $dispatcher = null)
    {
        $this->dispatcher = $dispatcher ?: new Dispatcher;
        $this->registerInternalEvents();
    }

    /**
     * Register a function to listen for a event to be fired.
     *
     * @param  string $event
     * @param  mixed  $function
     * @param  array  $parameters
     * @return this
     */
    public function listen(string $event, $function, $parameters = [])
    {
        // Save the function as a listener for the event. The first item is
        // the function to execute and the second is the parameters array.
        // A listeneter can be registered even without an existent event, but
        // if in any case the event is registered the listener will be called.
        $this->listeners[$event][] = [$function, is_array($parameters) ?
            $parameters : [$parameters], ];
    }

    /**
     * Return all the registered events.
     *
     * @param  bool $excludeInternals
     * @return array
     */
    public function getEvents(bool $excludeInternals = false)
    {
        $events = array_filter(array_keys($this->listeners), function ($event) {

            // Removes the ones that is in the list of terminated events.
            return ! in_array($event, $this->terminatedEvents);
        });

        if (! $excludeInternals) {

            // Add the internal events on the list of the public events.
            $events = array_merge($events, $this->internalEvents);
        }

        return $events;
    }

    /**
     * Return a list will all listeners of the given event.
     *
     * @param  string $event
     * @return array
     */
    public function getListeners(string $event)
    {
        if (in_array($events, $this->getEvents())) {
            return $this->listeners[$event];
        }
    }

    /**
     * Fire a given event and notify the listeners.
     *
     * @param  string $event
     * @param  mixed  $attachments
     * @return void
     */
    public function fire(string $event, $attachments = [])
    {
        // Notify listeners that an event was fired.
        $this->fireInternal(static::FIRED, [$event, $attachments]);
    }

    /**
     * Dispatch each listener function.
     *
     * @param  string $event
     * @param  array  $attachments
     * @return void
     */
    protected function notifyListeners(string $event, array $attachments)
    {
        foreach ($this->listeners[$event] as $listener) {
            try {

                // Add parameters to attachments.
                $attachments = array_merge((array) $attachments, (array) $listener[1]);

                // Dispatch the listener function through the dispatcher.
                $this->dispatcher->dispatch($listener[0], $attachments);
            } catch (Exception $ex) {
                if (in_array(static::ON_EXCEPTION, $this->getEvents())) {

                    // Call the on exception internal event.
                    $this->fire(static::ON_EXCEPTION, $ex);
                } else {

                    // Throw the unhandled exception.
                    throw $ex;
                }
            }
        }

        // Terminate the event.
        $this->fireInternal(static::TERMINATED, [$event]);
    }

    /**
     * Register all the internal events. The internal events are essential for
     * the event operation. The.
     *
     * @return void
     */
    protected function registerInternalEvents()
    {
        // Execute the internal fired event.
        $this->listen(static::FIRED, function ($event, $attachments) {

            // Receive an event as parameter when and event has been fired.
            // Then we call the event resolver to dispatch a registered event.
            $this->resolveEvent($event, $attachments);
        });

        // Execute the internal terminated event.
        $this->listen(static::TERMINATED, function ($event) {

            // Receive an event that had been terminated and must be locked.
            $this->terminate($event);
        });

        // Define the reserved internal events.
        $this->internalEvents = [
            static::FIRED,
            static::ON_EXCEPTION,
            static::TERMINATED,
        ];
    }

    /**
     * Resolve an event been fired.
     *
     * @param  string $event
     * @param  mixed  $attachments
     * @return void
     */
    protected function resolveEvent(string $event, $attachments = [])
    {
        // Only public registered events can be fired from outside manager.
        // Any call to not registred events will result in anything.
        if (in_array($event, $this->getEvents(true))) {

            // Notifyl listeners of an event.
            $this->notifyListeners($event, is_array($attachments) ?
                $attachments : [$attachments]);
        }
    }

    /**
     * Lock the event for the manager. A locked event can not be called
     * again. And the listeners will be not avaiable anymore.
     *
     * @param  string $event
     * @return void
     */
    protected function terminate(string $event)
    {
        if (in_array($event, $this->getEvents(true))) {

            // Register a new terminated event. Only apply for public events.
            // Internal events are not terminated.
            $this->terminatedEvents[] = $event;
        }
    }

    /**
     * Fire an internal event on the manager.
     *
     * @param  string          $event
     * @param  mixed           $attachments
     * @return void
     *
     * @throws InvalidArgument If the event does not exist.
     */
    protected function fireInternal(string $event, $attachments)
    {
        if (in_array($event, $this->internalEvents)) {

            // Call the event reslver internally.
            $this->resolveEvent($event, $attachments);
        } else {
            throw new InvalidArgument("Unknow internal event [$event].");
        }
    }
}
