<?php
namespace i350\Fields;

use Illuminate\Support\Str;
use JsonSerializable;

const V_UNSET = PHP_INT_MIN;

abstract class BaseField implements IField, JsonSerializable
{
    protected ?IField $previousField = null;

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
        protected bool $fillable = false,
        protected ?string $virtual_as = null,
        protected ?string $stored_as = null,
        protected ?string $charset = null,
        protected bool $translatable = false,
        protected array $allowed = [],
        protected array $validation_rules = [],
        protected array $foreign_key = []
    )
    {
        if ($this->primary_key) {
            $this->unique = false;
            $this->nullable = false;
            $this->required = true;
        }

        if($this->increment) {
            $this->primary_key = false;
        }

        if($this->default === null) {
            $this->nullable = true;
        }
        elseif(($this->default === V_UNSET) && ($this->nullable === true) && ($this->required=false)) {
            $this->default = null;
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
    public function getDefault(): int|float|string|\Illuminate\Database\Query\Expression|null
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
    public function isFillable(): bool
    {
        return $this->fillable;
    }

    /**
     * @return string|null
     */
    public function getCharset(): ?string
    {
        return $this->charset;
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
                '1' =>  fn(&$commands, &$extra) => $commands['nullable']="nullable()",
                '-1' =>  fn(&$commands, &$extra) => $commands['nullable']="nullable(false)",
            ],
            'increment'   =>  [
                '1' =>  fn(&$commands, &$extra) => $commands['increment']="autoIncrement()",
            ],
        ];
        $paramActions = $map[$param] ?? [];
        return $paramActions[$change] ?? null;
    }
    public function toMigration(bool $forceChange = false): array
    {
        $commands = [];
        $extraCommands = [];

        if (empty($this->getDirtyParams())) return [];


        # TODO: Create command object instead of string
        if($this->getType()==='foreign_key') {
            /** @var ForeignKeyField $this */
            $commands['type'] = "foreignIdFor(\\{$this->getModel()}::class)";
        } else {
            $lengthArg = $this->getMaxLength() != V_UNSET ? ", {$this->getMaxLength()}" : '';
            $commands['type'] = "{$this->getType()}('{$this->getName()}'{$lengthArg})";
        }

        // Handle Charset
        if($this->getCharset()) {
            $commands['charset'] = "charset('{$this->getCharset()}')";
        }

        // Handle Boolean params
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
                    case 'Illuminate\Database\Query\Expression':
                        $commands['default'] = "default(\DB::raw('{$default->getValue(DB::getQueryGrammar())}'))";
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

        // Handle computed columns
        foreach(['virtual_as', 'stored_as'] as $computedAction) {
            if($this->isDirty($computedAction)) {
                $commands[] = Str::camel($computedAction)."('{$this->$computedAction}')";
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


    public function toValidation(bool $create = true, string $table = null): ?string
    {
        if (!$this->isFillable())    return null;
        $rules = [];
        if ($this->isRequired()) {
            if ($create)    {
                if($this->isTranslatable()) {
                    if (config('fields.i18n.all_required')) {
                        $rules[] = 'required';
                    } else {
                        $siblings = [];
                        $nameParts = explode('_', $this->getName());
                        $baseName = str_replace("_" . end($nameParts), '', $this->getName());
                        foreach(config('fields.i18n.locals') as $locale) {
                            $siblingName = "{$baseName}_$locale";
                            if ($siblingName != $this->getName())   $siblings[] = $siblingName;
                        }
                        $rules[] = 'required_without:' . implode(',', $siblings);
                    }
                } else {
                    $rules[] = 'required';
                }
            }
        } else {
            if ($this->isNullable())    $rules[] = 'nullable';
        }
        switch(static::class) {
            case BooleanField::class:
                $rules[] = 'boolean';
                break;
            case IntegerField::class:
            case BigIntegerField::class:
                $rules[] = 'numeric';
                break;
            case StringField::class:
            case TextField::class:
            case MediumTextField::class:
            case LongTextField::class:
                $rules[] = 'string';
                break;
        }
        if ($this->isUnique() && $table) {
            $rules[] = "unique:" . $table . ',' . $this->getName();
        }
        if ($this->getMaxLength() !== V_UNSET) {
            $rules[] = 'max:' . $this->getMaxLength();
        }
        if ($fk = $this->getForeignKey()) {
            $rules[] = 'exists:' . $fk['on'] . ',' . $fk['references'];
        }
        return implode("|", $rules);
    }

    public function toArray(bool $migration=false): array
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
        $originalParams = $this->previousField?array_map(fn($v) => is_array($v) ? json_encode($v) : $v, $this->getPreviousField()->jsonSerialize()):[];
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
