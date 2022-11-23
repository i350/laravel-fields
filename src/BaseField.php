<?php
namespace i350\Fields;

use JsonSerializable;

const V_UNSET = PHP_INT_MIN;

abstract class BaseField implements IField, JsonSerializable
{
    private ?IField $previousField = null;

    public function __construct(
        protected string $name,
        protected string $type,
        protected int $max_length = V_UNSET,
        protected bool $primary_key = false,
        protected bool $increment = false,
        protected bool $unsigned = false,
        protected bool $unique = false,
        protected bool $nullable = false,
        protected $default = V_UNSET,
        protected bool $required = false,
        protected bool $translatable = false,
        protected array $allowed = [],
        protected array $validation_rules = [],
        protected array $foreign_key = []
    )
    {
        if ($this->primary_key) {
            $this->unique = true;
            $this->nullable = false;
            $this->required = true;
        }

        if($this->default === null) {
            $this->nullable = true;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getMaxLength(): int
    {
        return $this->max_length;
    }

    /**
     * @return bool
     */
    public function isPrimaryKey(): bool
    {
        return $this->primary_key;
    }

    /**
     * @return bool
     */
    public function isIncrement(): bool
    {
        return $this->increment;
    }

    /**
     * @return bool
     */
    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    /**
     * @return bool
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

    /**
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * @return int|float|string|null
     */
    public function getDefault(): int|float|string|null
    {
        return $this->default;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return bool
     */
    public function isTranslatable(): bool
    {
        return $this->translatable;
    }

    /**
     * @return array
     */
    public function getValidationRules(): array
    {
        return $this->validation_rules;
    }


    /**
     * @return array
     */
    public function getForeignKey(): array
    {
        return $this->foreign_key;
    }

    /**
     * @return IField|null
     */
    public function getPreviousField(): ?IField
    {
        return $this->previousField ?: new static(name: $this->getName());
    }

    /**
     * @param string $name
     * @return BaseField
     */
    public function setName(string $name): BaseField
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $type
     * @return BaseField
     */
    public function setType(string $type): BaseField
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param int $max_length
     * @return BaseField
     */
    public function setMaxLength(int $max_length): BaseField
    {
        $this->max_length = $max_length;
        return $this;
    }

    /**
     * @param bool $primary_key
     * @return BaseField
     */
    public function setPrimaryKey(bool $primary_key): BaseField
    {
        $this->primary_key = $primary_key;
        return $this;
    }

    /**
     * @param bool $increment
     * @return BaseField
     */
    public function setIncrement(bool $increment): BaseField
    {
        $this->increment = $increment;
        return $this;
    }

    /**
     * @param bool $unsigned
     * @return BaseField
     */
    public function setUnsigned(bool $unsigned): BaseField
    {
        $this->unsigned = $unsigned;
        return $this;
    }

    /**
     * @param bool $unique
     * @return BaseField
     */
    public function setUnique(bool $unique): BaseField
    {
        $this->unique = $unique;
        return $this;
    }

    /**
     * @param bool $nullable
     * @return BaseField
     */
    public function setNullable(bool $nullable): BaseField
    {
        $this->nullable = $nullable;
        return $this;
    }

    /**
     * @param int $default
     * @return BaseField
     */
    public function setDefault(int $default): BaseField
    {
        $this->default = $default;
        return $this;
    }

    /**
     * @param bool $required
     * @return BaseField
     */
    public function setRequired(bool $required): BaseField
    {
        $this->required = $required;
        return $this;
    }


    /**
     * @param array $foreign_key
     * @return BaseField
     */
    public function setForeignKey(array $foreign_key): BaseField
    {
        $this->foreign_key = $foreign_key;
        return $this;
    }

    /**
     * @param bool $translatable
     * @return BaseField
     */
    public function setTranslatable(bool $translatable): BaseField
    {
        $this->translatable = $translatable;
        return $this;
    }

    /**
     * @param array $validation_rules
     * @return BaseField
     */
    public function setValidationRules(array $validation_rules): BaseField
    {
        $this->validation_rules = $validation_rules;
        return $this;
    }

    public function setPreviousField(IField $field): BaseField {
        $this->previousField = $field;
        return $this;
    }


    protected static function getMigrationAction($param, $change): ?\Closure
    {
        $map = [
            'primary_key'   =>  [
                '1' =>  fn(&$commands, &$extra) => $commands['primary_key']="primary()",
            ],
            'unique'   =>  [
                '1' =>  fn(&$commands, &$extra) => $commands['unique']="unique()",
            ],
            'unsigned'   =>  [
                '1' =>  fn(&$commands, &$extra) => $commands['unsigned']="unsigned()",
            ],
            'nullable'   =>  [
                '1' =>  fn(&$commands, &$extra) => $commands['nullable']="nullable(true)",
                '-1' =>  fn(&$commands, &$extra) => $commands['nullable']="nullable(false)",
            ],
            'increment'   =>  [
                '1' =>  fn(&$commands, &$extra) => $commands['increment']="autoIncrement()",
            ],
        ];
        $paramActions = $map[$param] ?? [];
        return $paramActions[$change] ?? null;
    }
    public function toMigration($forceChange = false): array
    {
        $commands = [];
        $extraCommands = [];

        if (empty($this->getDirtyParams())) return [];

        $lengthArg = $this->getMaxLength() != V_UNSET ? ", ['length' => {$this->getMaxLength()}]" : '';
        # TODO: Create command object instead of string
        $commands['type'] = "addColumn('{$this->getType()}', '{$this->getName()}'{$lengthArg})";

        $comparison = $this->compareParams();
        foreach($comparison as $param => $change) {
            $action = $this::getMigrationAction($param, $change);
            if(!is_null($action)) {
                $action($commands, $extraCommands);
            }
        }

        // Handle default value
        if($this->isDirty('default') && $this->getDefault() !== V_UNSET) {
            if($this->getType() === 'timestamp') {
                if ($this->getDefault() === TimestampField::USE_CURRENT) {
                    $commands['default'] = "useCurrent()";
                } elseif ($this->getDefault() === TimestampField::USE_CURRENT_ON_UPDATE) {
                    $commands['default'] = "useCurrentOnUpdate()";
                }
            }

            if(!isset($commands['default'])) {
                switch(get_debug_type($this->getDefault())) {
                    case 'null':
                        if (!$this->isNullable()) $commands['default'] = "default(null)";
                        break;
                    case 'string':
                        $commands['default'] = "default('{$this->getDefault()}')";
                        break;
                    case 'int':
                    case 'float':
                    case 'bool':
                        $commands['default'] = "default({$this->getDefault()})";
                        break;
                }
            }
        }

        // Handle foreign key
        if($this->isDirty('foreign_key')) {
            if($this->getPreviousField()->getForeignKey()) {
                $extraCommands[] = "\$table->dropForeign('{$this->getPreviousField()->getName()}');";
            }
            if($this->getForeignKey()) {
                $parts = ['$table', "foreign('{$this->getName()}')"];
                foreach($this->getForeignKey() as $method => $arg) {
                    $parts[] = "$method('$arg')";
                }
                $extraCommands[] = implode("->", $parts) . ';';
            }
        }
        return array_merge(['$table->' . implode("->", $commands) . ($this->previousField||$forceChange?'->change()':'') .  ';'], $extraCommands);
    }

    public function toDownMigration(): array
    {
        if ($this->previousField)   {
            $this->getPreviousField()->setPreviousField($this);
            return $this->getPreviousField()->toMigration(true);
        }
        $commands = [];
        if ($this->getForeignKey()) {
            $commands[] = "\$table->dropForeign('{$this->getName()}');";
        }
        if ($this->isPrimaryKey()) {
            $commands[] = "\$table->dropPrimary('{$this->getName()}');";
        }
        if ($this->isUnique()) {
            $commands[] = "\$table->dropUnique('{$this->getName()}');";
        }
        // TODO: $table->dropIndex()
        $commands[] = "\$table->dropColumn('{$this->getName()}');";
        return $commands;
    }

    public function jsonSerialize(): array
    {
        $params = get_object_vars($this);
        unset($params['validation_rules']);
        unset($params['translatable']);
        unset($params['required']);
        unset($params['previousField']);
        return array_filter($params, fn($value) => $value !== V_UNSET);
    }


    public function toValidation()
    {

    }

    public function toArray($migration=false): array
    {
        $array = [];
        foreach (get_class_vars(static::class) as $k=>$v) {
            if ($migration && in_array($k, ['required', 'validation_rules', 'translatable']))   continue;
            $value = $this->{$k};
            if ($value === V_UNSET) continue;
            $array[$k] = $this->{$k};
        }
        return $array;
    }

    public function getDirtyParams(): array {
        $originalParams = array_map(fn($v) => is_array($v) ? json_encode($v) : $v, $this->getPreviousField()->jsonSerialize());
        $newParams = array_map(fn($v) => is_array($v) ? json_encode($v) : $v, $this->jsonSerialize());
        return array_keys(array_diff($originalParams, $newParams) + array_diff($newParams, $originalParams));
    }

    public function isDirty(string $parameter): bool {
        return $this->compareParam($parameter) !== 0;
    }

    public function compareParams(): array {
        $dirtyParams = $this->getDirtyParams();

        $result = [];
        foreach($dirtyParams as $parameter) {
            $result[$parameter] = $this->compareParam($parameter);
        }
        return $result;
    }

    public function compareParam(string $parameter): int {
        $previousValue = $this->getPreviousField()->{$parameter};
        $currentValue = $this->{$parameter};

        return $this->compare($currentValue, $previousValue);
    }

    public function compare($newVal, $originalVal): int {
        if ($newVal === $originalVal)   return 0;
        if ($originalVal === V_UNSET)  return 1;
        if ($newVal === V_UNSET)  return -1;

        return $newVal <=> $originalVal;
    }

}