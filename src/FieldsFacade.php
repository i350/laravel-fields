<?php

namespace i350\Fields;

use Illuminate\Support\Facades\Facade;

/**
 * @see \i350\Fields
 */
class FieldsFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'fields';
    }
}
