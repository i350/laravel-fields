<?php

namespace i350\Fields\Console;

use i350\Fields\IField;
use i350\Fields\Traits\LaravelFields;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ValidationRulesFillerCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'fields:fill:rules {model : The name of the model} {request : The name of FormRequest class}
        {--update : Indicate if the request is an update request}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill FormRequest with validation rules of a model';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new migration install command instance.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $model = trim($this->input->getArgument('model'));
        if (!class_exists($model)) {
            $model = "\\App\\Models\\$model";

            if (!class_exists($model)) {
                throw new \Exception("Cannot find model $model");
            }
        }

        $request = trim($this->input->getArgument('request'));

        $update = $this->input->getOption('update') ?: Str::startsWith(class_basename($request), "Update");

        $this->writeValidationRules($model, $request, $update);
    }

    /**
     * Write the migration file to disk.
     *
     * @param string $model
     * @param $request
     * @param $update
     * @return void
     * @throws \Exception
     */
    protected function writeValidationRules($model, $request, $update)
    {
        $fileContent = $this->files->get($request);
        $validationRules = [];
        $table = (new $model)->getTable();
        /** @var LaravelFields $model */
        foreach($model::getFields() as $field) {

            /** @var IField $field */
            $rules = $field->toValidation(!$update, $table);
            if (!is_null($rules)) {
                $validationRules[] = "'{$field->getName()}' => \"{$rules}\",\n";
            }
        }

        $search = config('fields.ruler_filler.search');
        $separator = config('fields.ruler_filler.separator');
        $fileContent = str_replace($search, implode($separator, $validationRules), $fileContent);

        $this->files->put($request, $fileContent);


        $this->line("<info>Validation Rules added to the FormRequest:</info> {$request}");
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath()
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return ! $this->usingRealPath()
                ? $this->laravel->basePath().'/'.$targetPath
                : $targetPath;
        }

        return parent::getMigrationPath();
    }
}
