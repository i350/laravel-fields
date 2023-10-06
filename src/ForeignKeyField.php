<?php

namespace i350\Fields;

use Illuminate\Database\Eloquent\Model;

class ForeignKeyField extends BaseField
{
    public function __construct(
        protected string $name,
        protected string $model,
        protected ?string $on_delete = null,
        protected ?string $on_update = null,
        protected bool $nullable = false,
    )
    {
        $args = get_defined_vars();
        unset($args['model']);
        unset($args['on_delete']);
        unset($args['on_update']);
        $args['type'] = 'foreign_key';

        if(($on_delete=='set null')||($on_update=='set null')||($this->nullable)) {
            $args['nullable']=true;
        }

//        return $model->getKeyType() === 'int' && $model->getIncrementing()
//            ? $this->foreignId($column ?: $model->getForeignKey())
//            : $this->foreignUuid($column ?: $model->getForeignKey());
//

        /** @var Model $model */
        $model = new $model;
        $args['foreign_key'] = ForeignKey::define($model->getKeyName(), $model->getTable(), $on_delete, $on_update);
        parent::__construct(...$args);
    }

    public function getPreviousField(): ?IField
    {
        return $this->previousField ?: (new static(
            name: $this->name,
            model: $this->model,
            on_delete: $this->on_delete,
        ))->setForeignKey([]);
    }

    public function getModel(): string
    {
        return get_class(new $this->model);
    }


}