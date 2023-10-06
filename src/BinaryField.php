<?php

namespace i350\Fields;


class BinaryField extends BaseField
{
    public function __construct(
        protected string $name,
        protected int $max_length = 16,

        protected bool $primary_key = false,
        protected bool $increment = false,
        protected bool $unique = false,
        protected bool $nullable = false,
        protected $default = V_UNSET,
        protected bool $required = false,
        protected bool $fillable = false,
        protected ?string $virtual_as = null,
        protected ?string $stored_as = null,
        protected array $validation_rules = [],
        protected array $foreign_key = []
    )
    {
        $args = get_defined_vars();
        $args['type'] = 'char';
        $args['charset'] = 'binary';
        parent::__construct(...$args);
    }
}