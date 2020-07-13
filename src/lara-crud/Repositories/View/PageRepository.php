<?php

namespace LaraCrud\Repositories\View;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LaraCrud\Contracts\TableContract;
use LaraCrud\Services\FullTextSearch;
use Laravel\Scout\Searchable;

abstract class PageRepository
{
    /**
     * The Main Model.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var TableContract
     */
    protected TableContract $table;

    /**
     * Parent Model if any.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $parentModel;

    /**
     * List of routes that are registered for CRUD operation of this Model.
     *
     * @var array
     */
    protected array $routes = [];

    /**
     * Root path for view file. e.g. base_path('resources/views').
     *
     * @var string
     */
    protected string $viewRootPath;

    /**
     * View namespace if it is a package.
     *
     * @var string
     */
    protected string $viewNamespace;

    /**
     * @var string
     */
    protected string $parentModelShortName;

    /**
     * @var string
     */
    protected string $modelShortName;

    /**
     * @var \SplFileObject
     */
    protected $splFileObject;

    /**
     * PageRepository constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->table = app(TableContract::class, ['table' => $this->model->getTable()]);

        $this->viewNamespace = config('laracrud.view.namespace');
        $this->viewRootPath = config('laracrud.view.path');
    }

    /**
     * Save generated code into a blade file.
     *
     * @return string
     */
    public function save(): string
    {
        return $this->path();
    }

    public function title(): string
    {
        return $this->table->label();
    }

    /**
     * Relative path of the file. E.g. pages.posts.index .
     *
     * @return string
     */
    abstract public function path(): string;

    /**
     * @return string
     */
    abstract public function template(): string;

    /**
     * Check whether view file is already exists or not.
     *
     * @return bool
     */
    public function isExists(): bool
    {
        return file_exists($this->viewRootPath . '/' . $this->path());
    }

    /**
     * Where Model is a softDelete able modal. If so then we should implement Trash box and restore button.
     *
     * @return bool
     */
    public function isSoftDeleteAble(): bool
    {
        return in_array(SoftDeletes::class, class_uses($this->model));
    }

    /**
     * If Model implement Scot search then we should show a search form on index page.
     *
     * @return bool
     */
    public function isSearchAble(): bool
    {
        $traits = class_uses($this->model);

        return in_array(Searchable::class, $traits) || in_array(FullTextSearch::class, $traits);
    }

    /**
     * @return \LaraCrud\Contracts\TableContract
     */
    public function table(): TableContract
    {
        return $this->table;
    }

    /**
     * Set Parent Model.
     *
     * @param \Illuminate\Database\Eloquent\Model $parentModel
     */
    public function setParent(Model $parentModel)
    {
        $this->parentModel = $parentModel;
    }

    /**
     * Get Model class name without namespace.
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    protected function getModelShortName(): string
    {
        if (!empty($this->modelShortName)) {
            return $this->modelShortName;
        }

        return $this->modelShortName = lcfirst((new \ReflectionClass($this->model))->getShortName());
    }

    /**
     * Get Model class Name without namespace.
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    protected function getParentShortName(): string
    {
        if (!empty($this->parentModelShortName)) {
            return $this->parentModelShortName;
        }

        return $this->parentModelShortName = lcfirst((new \ReflectionClass($this->parentModel))->getShortName());
    }
}
