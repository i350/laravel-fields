<?php

namespace i350\Fields;

class BooleanField extends BaseField
{
    public function __construct(
        protected string $name,

        protected bool $primary_key = false,
        protected bool $unique = false,
        protected bool $nullable = false,
        protected $default = V_UNSET,
        protected bool $required = false,
        protected bool $fillable = false,
        protected ?string $virtual_as = null,
        protected ?string $stored_as = null,
        protected array $validation_rules = [],
    )
    {
        $args = get_defined_vars();
        $args['type'] = 'boolean';
        parent::__construct(...$args);
    }
}