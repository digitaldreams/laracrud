<?php

namespace LaraCrud\View;

use DbReader\Column;
use DbReader\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use Route;

abstract class Page implements Crud
{
    use Helper;
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var string
     */
    protected $name;

    /**
     * Type of page e.g. modal, table, tabpan
     * @var string
     */
    protected $type;

    /**
     * Name of the parent folder where file will be saved.
     * @var
     */
    protected $folder;

    /**
     * Bootstrap Version
     * @var
     */
    protected $version;

    /**
     * @var
     */
    protected $filePath;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $resource_path;

    /**
     * Routes Map
     *
     * @var
     */
    public static $routeMap = [];

    /**
     * Current Controller Name
     * @var string
     */
    public static $controller;

    /**
     * @var Model
     */
    public $model;


    /**
     * @var
     */
    public static $policy;

    /**
     * @var array
     */
    protected $dataStore = [];

    /**
     * Page constructor.
     */
    public function __construct()
    {
        $this->version = config('laracrud.view.bootstrap');
        $this->resource_path = config('laracrud.view.path');

        $this->filePath = rtrim($this->resource_path, "/") . "/" . $this->folder . "/" . $this->name . ".blade.php";
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        if (file_exists($this->filePath)) {
            throw  new \Exception($this->name . ' already exists');
        }
        $folder = rtrim($this->resource_path, "/") . "/" . $this->folder;
        if (!file_exists($folder)) {
            mkdir($folder);
        }
        $table = new \SplFileObject($this->filePath, 'w+');
        $table->fwrite($this->template());
    }

    /**
     *  Assign Folder Name
     */
    public function setFolderName()
    {
        $pagePath = config('laracrud.view.page.path');
        if (!empty($pagePath)) {
            $folder = rtrim(config('laracrud.view.path'), "/") . "/" . $pagePath;
            if (!file_exists($folder)) {
                mkdir($folder);
            }
            $this->folder = trim($pagePath, "/") . "/" . $this->table->name();
        } else {
            $this->folder = $this->table->name();
        }
    }

    /**
     * Whether current file exists or now.
     */
    public function isExists()
    {
        return file_exists($this->filePath);
    }

    /**
     * @return mixed
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param $filePath full path
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $method
     * @param string $table
     * @return mixed|string
     */
    public static function getRouteName($method, $table = '')
    {
        $routePrefix = config('laracrud.route.prefix');
        $table = !empty($routePrefix) ? $routePrefix . $table : $table;
        $action = static::$controller . "@" . $method;
        if (isset(static::$routeMap[$action])) {
            return static::$routeMap[$action];
        }
        return $table . "." . $method;
    }

    /**
     * @return array
     */
    public static function fetchRoute()
    {
        $routes = Route::getRoutes();
        foreach ($routes as $route) {
            static::$routeMap[$route->getActionName()] = $route->getName();
        }
        return static::$routeMap;
    }

    protected function isIgnoreAble(Column $column)
    {
        if ($column->isIgnore() || $column->isProtected()) {
            return true;
        }
        $fillable = $this->model->getFillable();
        $guarded = $this->model->getGuarded();

        if (!in_array($column->name(), $fillable) || in_array($column->name(), $guarded)) {
            return true;
        }
        return false;
    }

    public function getTitleColumn()
    {
        $titles = config('laracrud.view.titles', []);
        return isset($titles[$this->table->name()]) ? $titles[$this->table->name()] : 'id';
    }
}
