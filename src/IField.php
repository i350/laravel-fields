<?php

namespace i350\Fields;

Interface IField
{

    public function getName(): string;

    public function getType(): string;

    public function getMaxLength(): int;

    public function isPrimaryKey(): bool;

    public function isIncrement(): bool;

    public function isUnique(): bool;

    public function isNullable(): bool;

    public function getDefault(): int|float|string|\Illuminate\Database\Query\Expression|null;

    public function isRequired(): bool;

    public function isTranslatable(): bool;

    public function getValidationRules(): array;

    public function getPreviousField(): ?IField;

    public function setName(string $name): BaseField;

    public function setType(string $type): BaseField;

    public function setMaxLength(int $max_length): BaseField;

    public function setPrimaryKey(bool $primary_key): BaseField;

    public function setIncrement(bool $increment): BaseField;

    public function setUnique(bool $unique): BaseField;

    public function setNullable(bool $nullable): BaseField;

    public function setDefault(int $default): BaseField;

    public function setRequired(bool $required): BaseField;

    public function setTranslatable(bool $translatable): BaseField;

    public function setValidationRules(array $validation_rules): BaseField;

    public function setPreviousField(IField $field): BaseField;

    public function toMigration(): array;

    public function toDownMigration(): array;

    public function jsonSerialize(): array;

}