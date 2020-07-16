<?php

namespace LaraCrud\Crud;

use DbReader\Column;
use DbReader\Table;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;

class Request implements Crud
{
    use Helper;
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Request Class name. Defaut is single version of database table.
     *
     * @var string
     */
    protected $modelName;

    /**
     * Request Class parent Namespace.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Template path either api or web.
     *
     * @var string
     */
    protected $template;

    protected $authorization = 'true';

    /**
     * RequestCrud constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string                              $name
     * @param bool                                $api
     */
    public function __construct(\Illuminate\Database\Eloquent\Model $model, $name = '', $api = false)
    {
        $this->model = $model;
        $this->table = new Table($model->getTable());
        $this->namespace = !empty($api) ? config('laracrud.request.apiNamespace') : config('laracrud.request.namespace');
        $this->namespace = $this->getFullNS($this->namespace);
        $this->modelName = $this->getModelName($model->getTable());
        if (!empty($name)) {
            $this->parseName($name);
        } else {
            $this->modelName .= config('laracrud.request.classSuffix');
        }
        $this->template = !empty($api) ? 'api' : 'web';
    }

    /**
     * Save code to file.
     */
    public function save()
    {
        $filePath = $this->checkPath();
        if (file_exists($filePath)) {
            throw new \Exception($this->namespace . '\\' . $this->modelName . ' already exists');
        }
        $model = new \SplFileObject($filePath, 'w+');
        $model->fwrite($this->template());
    }

    /**
     * Generate complete code.
     *
     * @return string
     */
    public function template()
    {
        $tempMan = new TemplateManager('request/' . $this->template . '/template.txt', [
            'namespace' => $this->namespace,
            'requestClassName' => $this->modelName,
            'authorization' => $this->authorization,
            'rules' => implode("\n", $this->makeRules()),
        ]);

        return $tempMan->get();
    }

    /**
     * @return array
     */
    public function makeRules()
    {
        $rules = [];
        $columns = $this->table->columnClasses();
        $fillable = $this->model->getFillable();
        $guarded = $this->model->getGuarded();
        foreach ($columns as $column) {
            if (in_array($column->name(), config('laracrud.model.protectedColumns'))) {
                continue;
            } elseif (!in_array($column->name(), $fillable) || in_array($column->name(), $guarded)) {
                continue;
            }
            $rules[] = "\t\t\t'{$column->name()}' => '" . implode('|', $this->rule($column)) . "',";
        }

        return $rules;
    }

    /**
     * Make rules for Request Class.
     *
     * @param Column $column
     *
     * @return array
     */
    public function rule(Column $column)
    {
        $rules = [];
        if (!$column->isNull()) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        if ($column->isUnique()) {
            $rules[] = "unique:{$this->table->name()},{$column->name()}";
        }
        if ($column->isForeign()) {
            $rules[] = "exists:{$column->foreignTable()},{$column->foreignColumn()}";
        }
        if ('enum' == $column->type()) {
            $rules[] = 'in:' . implode(',', $column->options());
        } elseif ($column->isFile()) {
            $rules[] = 'file';
        } elseif (in_array($column->type(), ['varchar'])) {
            $rules[] = 'max:' . $column->length();
        } elseif ('tinyint' == $column->type() && 1 == $column->length()) {
            $rules[] = 'boolean';
        } elseif (in_array($column->type(), ['smallint', 'int', 'mediumint', 'bigint', 'decimal', 'float', 'double'])) {
            $rules[] = 'numeric';
        } elseif (in_array($column->type(), ['date', 'time', 'datetime', 'timestamp'])) {
            $rules[] = 'date';
        }

        if (in_array($column->type(), ['text', 'tinytext', 'mediumtext', 'longtext'])) {
            $rules[] = 'string';
        }

        return $rules;
    }

    /**
     * @param $auth
     *
     * @return $this
     */
    public function setAuthorization($auth)
    {
        $this->authorization = $auth;

        return $this;
    }
}
