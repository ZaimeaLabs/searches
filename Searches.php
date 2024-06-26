<?php

declare(strict_types=1);

namespace ZaimeaLabs\Searches;

use Illuminate\Support\Traits\ForwardsCalls;

class Searches
{
    use ForwardsCalls;

    /**
     * Returns a new Builder instance.
     *
     * @return \ZaimeaLabs\Searches\Builder
     */
    public function new(): Builder
    {
        return new Builder;
    }

    /**
    * Handle dynamic method calls into a new Builder instance.
    *
    * @param  string  $method
    * @param  array  $parameters
    * @return mixed
    */
    public function __call($method, $parameters): mixed
    {
        return $this->forwardCallTo(
            $this->new(),
            $method,
            $parameters
        );
    }
}
