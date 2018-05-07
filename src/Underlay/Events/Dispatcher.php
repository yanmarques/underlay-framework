<?php

namespace Underlay\Events;

use Closure;
use TypeError;
use InvalidArgumentException;
use Underlay\Contracts\Dispatchable;

class Dispatcher implements Dispatchable
{
    /**
     * Dispatch the functions. If you are dispatching a list of functions
     * the parameters argument will be passed as parameter for each function
     * executed. Otherwise the paramaters will just be passed for the function.
     *
     * @param  string|Closure $function
     * @param  mixed          $parameters
     * @return mixed
     *
     * @throws InvalidArgument If the class of a string formatted do not exists.
     */
    public function dispatch($function, array $parameters = [])
    {
        if ($function instanceof Closure) {

            // Call the Closure as an anonymous function object.
            return $this->call($function, $parameters);
        }

        if (is_string($function)) {

            // Call the string function representated by the format.
            return $this->callFormattedString($function, $parameters);
        }

        // Fire exception because the $function type is invalid.
        throw new TypeError('Invalid type '.gettype($function).' for argument.');
    }

    /**
     * Handle a callable function to be called.
     *
     * @param  Closure $callback
     * @param  array   $parameters
     * @return mixed
     */
    protected function call(Closure $callback, array $parameters)
    {
        // Call the user function with the passed parameters.
        return call_user_func($callback, ...$parameters);
    }

    /**
     * Call a formatted string function. Format: <class namespace>@<method>.
     *
     * @param  string $functionFormat
     * @param  array  $parameters
     * @return mixed
     */
    protected function callFormattedString(string $functionFormat, array $parameters)
    {
        // Call the formatted string. Format: <class namespace>@<method name>.
        list($namespace, $method) = explode('@', $functionFormat);

        if (! class_exists($namespace)) {
            throw new InvalidArgumentException('The class to dispatch does not exists.');
        }

        // Instantiate the class and call the method with the $parameters as arguments.
        return (new $namespace())->{$method}(...$parameters);
    }
}
