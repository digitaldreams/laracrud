<?php

namespace LaraCrud;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContainerCrud
 *
 * @author Tuhin
 */
class ControllerCrud extends LaraCrud
{
    protected $controllerName;
    protected $modelName;
    protected $viewPath;
    protected $modelNameSpace = '\App';
    protected $requestClass   = 'Request';
    public $table;

    public function __construct($modelName = '')
    {
        $this->modelName = $this->modelNameSpace.'\\'.$modelName;
        // $this->getTableList();
        //$this->loadDetails();
        $this->init();
        $this->prepareRelation();
    }

    public function init()
    {
        if (!empty($this->modelName)) {

            $this->parseModelName();

            if (class_exists($this->modelName)) {
                $model       = new $this->modelName;
                $this->table = $table       = $model->getTable();

                $this->tables[] = $this->table;
                $this->loadDetails();

                $requestName = $this->getModelName($table);
                $fullName    = '\App\Http\Requests\\'.$requestName.'Request';
                if (class_exists($fullName)) {
                    $this->requestClass = $fullName;
                }
            }
        }
    }

    protected function parseModelName()
    {
        $class                = new \ReflectionClass($this->modelName);
        $this->modelNameSpace = $class->getNamespaceName();
        $this->viewPath       = strtolower($class->getShortName());
        $this->controllerName = $class->getShortName();
    }

    public function generateContent()
    {
        $contents = '';

        $contents = $this->getTempFile('controller.txt');
        $contents = str_replace("@@controllerName@@", $this->controllerName,
            $contents);
        $contents = str_replace("@@modelName@@", $this->modelName, $contents);
        $contents = str_replace("@@viewPath@@", $this->viewPath, $contents);


        $contents = str_replace("@@requestClass@@", $this->requestClass,
            $contents);
        $contents = str_replace("@@table@@", strtolower($this->getModelName($this->table)), $contents);

        $filterCode = $this->generateFilter();
        $contents   = str_replace("@@requestFiltetr@@", $filterCode, $contents);

        $contents = $this->checkRelation($this->table, $contents);

        return $contents;
    }

    public function make()
    {
        try {
            $controllerFileName = $this->controllerName.'Controller.php';
            $fullPath           = app_path('Http/Controllers/').$controllerFileName;

            if (!file_exists($fullPath)) {
                $modelContent = $this->generateContent();
                $this->saveFile($fullPath, $modelContent);
                return true;
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode(), $ex);
        }
        return false;
    }

    public function checkRelation($table, $contents)
    {
        $initialization = '';
        $variablePass   = '';
        $bmanySync      = '/**';
        if (isset($this->finalRelationShips[$table])) {
            foreach ($this->finalRelationShips[$table] as $rel) {
                if ($rel['name'] == static::RELATION_BELONGS_TO || $rel['name'] == static::RELATION_BELONGS_TO_MANY) {
                    $initialization .= '$'.strtolower($rel['model']).'='.'\\'.$this->modelNameSpace.'\\'.$rel['model']."::select(['id'])->get();"."\n";
                    $variablePass.='"'.strtolower($rel['model']).'"=>'.'$'.strtolower($rel['model']).','."\n";
                }
                if ($rel['name'] == static::RELATION_BELONGS_TO_MANY) {
                    $methodName = lcfirst($rel['model']);
                    $bmanySync.='$this->model->'.$methodName.'()->sync($request->get(\''.$rel['other_key'].'\',[]);'."\n";
                }
            }
        }
        $bmanySync.='*/';
        $contents = str_replace("@@belongsToRelation@@", $initialization,
            $contents);
        $contents = str_replace("@@belongsToRelationVars@@", $variablePass,
            $contents);
        $contents = str_replace("@@belongsToManyRelationSync@@", $bmanySync,
            $contents);
        return $contents;
    }

    protected function generateFilter()
    {
        $retCode = '';
        if (isset($this->tableColumns[$this->table])) {
            foreach ($this->tableColumns[$this->table] as $column) {
                $temp = $this->getTempFile('view/controller-filter.txt');
                if (in_array($column->Field, $this->protectedColumns)) {
                    continue;
                }

                $temp = str_replace('@@columnName@@', $column->Field, $temp);
                $temp = str_replace('@@scopeName@@', camel_case($column->Field),
                    $temp);
                $retCode.=$temp;
            }
        }
        return $retCode;
    }
}