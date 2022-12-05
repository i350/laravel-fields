<?php

namespace i350\Fields;

class TimestampField extends BaseField
{

    const USE_CURRENT = 'CURRENT_TIMESTAMP';
    const USE_CURRENT_ON_UPDATE = 'ON_UPDATE_CURRENT_TIMESTAMP';

    public function __construct(
        protected string $name,

        protected int $max_length = V_UNSET,
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
        $args['type'] = 'timestamp';
        parent::__construct(...$args);
    }
}