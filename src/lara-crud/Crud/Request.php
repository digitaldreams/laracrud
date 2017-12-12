<?php
/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

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
     * Request Class name. Defaut is single version of database table
     * @var string
     */
    protected $modelName;

    /**
     * Request Class parent Namespace.
     * @var string
     */
    protected $namespace;

    /**
     * Template path either api or web
     * @var string
     */
    protected $template;

    /**
     * RequestCrud constructor.
     * @param $table
     * @param string $name
     * @param bool $api
     */
    public function __construct($table, $name = '', $api = false)
    {
        $this->table = new Table($table);
        $this->namespace = !empty($api) ? config('laracrud.request.apiNamespace') : config('laracrud.request.namespace');
        $this->modelName = $this->getModelName($table);
        if (!empty($name)) {
            $this->parseName($name);
        } else {
            $this->modelName .= config('laracrud.request.classSuffix');
        }
        $this->template = !empty($api) ? 'api' : 'web';
    }

    /**
     * Generate complete code.
     * @return string
     */
    public function template()
    {
        $tempMan = new TemplateManager('request/'.$this->template.'/template.txt', [
            'namespace' => $this->namespace,
            'requestClassName' => $this->modelName,
            'rules' => implode("\n", $this->makeRules())
        ]);
        return $tempMan->get();
    }

    /**
     * Save code to file
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
     * @return array
     */
    public function makeRules()
    {
        $rules = [];
        $columns = $this->table->columnClasses();
        foreach ($columns as $column) {
            if (in_array($column->name(), config('laracrud.model.protectedColumns'))) {
                continue;
            }
            $rules[] = "\t\t'{$column->name()}'=>'" . implode("|", $this->rule($column)) . "',";
        }
        return $rules;
    }

    /**
     * Make rules for Request Class
     * @param Column $column
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
        if ($column->type() == 'enum') {
            $rules[] = "in:" . implode(",", $column->options());
        } elseif ($column->isFile()) {
            $rules[] = 'file';
        } elseif (in_array($column->type(), ['varchar'])) {
            $rules[] = "max:" . $column->length();
        } elseif ($column->type() == 'tinyint' && $column->length() == 1) {
            $rules[] = 'boolean';
        } elseif (in_array($column->type(), ['smallint', 'int', 'mediumint', 'bigint', 'decimal', 'float', 'double'])) {
            $rules[] = 'numeric';
        } elseif (in_array($column->type(), ['date', 'time', 'datetime', 'timestamp'])) {
            $rules[] = "date";
        }

        if (in_array($column->type(), ['text', 'tinytext', 'mediumtext', 'longtext'])) {
            $rules[] = "string";
        }
        return $rules;
    }
}