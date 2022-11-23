<?php

namespace i350\Fields\Traits;

use i350\Fields\IField;

/**
 * @method static getFields(): array
 */
trait LaravelFields
{
    public static function bootLaravelFields(): void
    {
        //
    }

    protected function initializeLaravelFields(): void
    {
        if(config('fields.i18n.auto_fill')) {
            $property = config('fields.i18n.property');
            if(property_exists($this, $property) && empty($this->{$property})) {
                $this->{$property} = array_map(fn(IField $field) => $field->getName(), $this->getTranslatableFields());
            }
        }
    }

    protected function getTranslatableFields(): array
    {
        return array_filter(static::getFields(), fn(IField $field) => $field->isTranslatable());
    }
}