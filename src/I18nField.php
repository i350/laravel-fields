<?php

namespace i350\Fields;

use Illuminate\Support\Collection;

class I18nField extends Collection
{
    public static function define(IField $field): array {
        $fields = [];
        foreach(config('fields.i18n.locals') as $local) {
            $localizedField = clone $field;
            $localizedField->setName("{$field->getName()}_$local");
            $localizedField->setTranslatable(true);
            $fields[] = $localizedField;
        }
        return $fields;
    }
}