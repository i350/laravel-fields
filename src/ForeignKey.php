<?php

namespace i350\Fields;

class ForeignKey extends BaseField
{
    public static function define(string $references, string $on, ?string $onDelete=null, ?string $onUpdate=null): array {
        return compact('references', 'on')
            + ($onDelete ? compact('onDelete') : [])
            + ($onUpdate ? compact('onUpdate') : []);
    }
}