<?php

namespace Underlay\Contracts;

interface Dispatchable
{
    /**
     * Dispatch an array of functions.
     *
     * @param  string|callable $functions
     * @param  array           $parameters
     * @return mixed
     */
    public function dispatch($function, array $parameters = []);
}
