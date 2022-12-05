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
            dump("[1]");
            $property = config('fields.i18n.property');
            if(property_exists($this, $property) && empty($this->{$property})) {
                $this->{$property} = array_map(fn(IField $field) => $field->getName(), $this->getTranslatableFields());
            }
        }
        if(config('fields.auto_fill_fillable')) {
            if(property_exists($this, 'fillable') && empty($this->fillable)) {
                $this->fillable = array_map(fn(IField $field) => $field->getName(), $this->getFillableFields());
            }
        }
    }

    protected function getTranslatableFields(): array
    {
        return array_filter(static::getFields(), fn(IField $field) => $field->isTranslatable());
    }

    protected function getFillableFields(): array
    {
        return array_filter(static::getFields(), fn(IField $field) => $field->isFillable());
    }
}