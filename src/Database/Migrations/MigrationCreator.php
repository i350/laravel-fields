<?php

namespace i350\Fields\Database\Migrations;

use App\Contracts\IModel;
use Closure;
use i350\Fields\IField;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MigrationCreator
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The custom app stubs directory.
     *
     * @var string
     */
    protected $customStubPath;

    /**
     * The registered post create hooks.
     *
     * @var array
     */
    protected $postCreate = [];

    /**
     * Create a new migration creator instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  string|null  $customStubPath
     * @return void
     */
    public function __construct(Filesystem $files, $customStubPath = null)
    {
        $this->files = $files;
        $this->customStubPath = $customStubPath;
    }

    /**
     * Create a new migration at the given path.
     *
     * @param  string  $model
     * @param  string  $name
     * @param  string  $path
     * @param  string|null  $table
     * @param  bool  $create
     * @return string
     *
     * @throws \Exception
     */
    public function create($model, $name, $path, $table = null, $create = false)
    {
        $this->ensureMigrationDoesntAlreadyExist($name, $path);

        // First we will get the stub file for the migration, which serves as a type
        // of template for the migration. Once we have those we will populate the
        // various place-holders, save the file, and run the post create event.
        $stub = $this->getStub($table, $create);

        $path = $this->getPath($name, $path);

        $this->files->ensureDirectoryExists(dirname($path));

        $upMigrations = $this->getModelUpMigrations($model);

        $downMigrations = $this->getModelDownMigrations($model);

        $this->files->put(
            $path, $this->populateStub($model, $name, $stub, $table, $upMigrations, $downMigrations)
        );

        // Next, we will fire any hooks that are supposed to fire after a migration is
        // created. Once that is done we'll be ready to return the full path to the
        // migration file so it can be used however it's needed by the developer.
        $this->firePostCreateHooks($table);

        return $path;
    }

    /**
     * Ensure that a migration with the given name doesn't already exist.
     *
     * @param  string  $name
     * @param  string  $migrationPath
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function ensureMigrationDoesntAlreadyExist($name, $migrationPath = null)
    {
        if (! empty($migrationPath)) {
            $migrationFiles = $this->files->glob($migrationPath.'/*.php');

            foreach ($migrationFiles as $migrationFile) {
                $this->files->requireOnce($migrationFile);
            }
        }

        if (class_exists($className = $this->getClassName($name))) {
            throw new InvalidArgumentException("A {$className} class already exists.");
        }
    }

    /**
     * Get the migration stub file.
     *
     * @param  string|null  $table
     * @param  bool  $create
     * @return string
     */
    protected function getStub($table, $create)
    {
        if (is_null($table)) {
            $stub = $this->files->exists($customPath = $this->customStubPath.'/migration.stub')
                ? $customPath
                : $this->stubPath().'/migration.stub';
        } elseif ($create) {
            $stub = $this->files->exists($customPath = $this->customStubPath.'/migration.create.stub')
                ? $customPath
                : $this->stubPath().'/migration.create.stub';
        } else {
            $stub = $this->files->exists($customPath = $this->customStubPath.'/migration.update.stub')
                ? $customPath
                : $this->stubPath().'/migration.update.stub';
        }

        return $this->files->get($stub);
    }

    /**
     * Populate the place-holders in the migration stub.
     *
     * @param  string  $model
     * @param  string  $name
     * @param  string  $stub
     * @param  string|null  $table
     * @param  array  $upMigrations
     * @param  array  $downMigrations
     * @return string
     */
    protected function populateStub($model, $name, $stub, $table, $upMigrations = [], $downMigrations = [])
    {
        $stub = str_replace(
            ['DummyClass', '{{ class }}', '{{class}}'],
            $this->getClassName($name), $stub
        );

        $stub = str_replace(
            '{{ upMigrations }}',
            implode("\n\t\t\t\t", $upMigrations), $stub
        );

        $stub = str_replace(
            '{{ downMigrations }}',
            implode("\n\t\t\t", $downMigrations), $stub
        );

        $stub = str_replace(
            '{{ fields }}',
            $this->getModelFieldsJson($model), $stub
        );

        // Here we will replace the table place-holders with the table specified by
        // the developer, which is useful for quickly creating a tables creation
        // or update migration from the console instead of typing it manually.
        if (! is_null($table)) {
            $stub = str_replace(
                ['DummyTable', '{{ table }}', '{{table}}'],
                $table, $stub
            );
        }

        return $stub;
    }

    /**
     * Get the class name of a migration name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getClassName($name)
    {
        return Str::studly($name);
    }

    /**
     * Get up migrations of model.
     *
     * @param  IModel|string  $model
     * @return array
     */
    protected function getModelUpMigrations($model, $previousFields = [])
    {
        $migrations = [];
        /** @var IField $field */
        foreach($model::getFields() as $field) {
            $previousFieldResult = array_filter(
                $previousFields,
                fn(IField $prevField) => $prevField->getName()===$field->getName()
            );

            if(!empty($previousFieldResult)) {
                $previousField = $previousFieldResult[array_key_first($previousFieldResult)];
                $field = $field->setPreviousField(clone $previousField);
            }
            $migrations = array_merge($migrations, $field->toMigration());
        }
        return $migrations;
    }

    /**
     * Get down migrations of model.
     *
     * @param  IModel|string  $model
     * @return array
     */
    protected function getModelDownMigrations($model, $previousFields = [])
    {
        $migrations = [];
        /** @var IField $field */
        foreach($model::getFields() as $field) {
            array_push($migrations, ...$field->toDownMigration());
        }
        return $migrations;
    }

    /**
     * Get json encoded of model fields.
     *
     * @param  string  $model
     * @return string
     */
    protected function getModelFieldsJson($model)
    {
        return json_encode($model::getFields());
    }

    /**
     * Get the full path to the migration.
     *
     * @param  string  $name
     * @param  string  $path
     * @return string
     */
    protected function getPath($name, $path)
    {
        return $path.'/'.$this->getDatePrefix().'_'.$name.'.php';
    }

    /**
     * Fire the registered post create hooks.
     *
     * @param  string|null  $table
     * @return void
     */
    protected function firePostCreateHooks($table)
    {
        foreach ($this->postCreate as $callback) {
            $callback($table);
        }
    }

    /**
     * Register a post migration create hook.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public function afterCreate(Closure $callback)
    {
        $this->postCreate[] = $callback;
    }

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath()
    {
        return __DIR__.'/stubs';
    }

    /**
     * Get the filesystem instance.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }
}
