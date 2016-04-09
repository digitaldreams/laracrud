<?php

namespace App\Libs;

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
class ControllerCrud extends LaraCrud {

    protected $controllerName;
    protected $modelName;
    protected $viewPath;
    protected $modelNameSpace='\App\Models';

    public function __construct($modelName = '') {
        $this->modelName = $modelName;
        $this->init();
        $this->getTableList();
        $this->loadDetails();
        $this->prepareRelation();
    }

    public function init() {
        if (!empty($this->modelName)) {
            $arr = explode('\\', $this->modelName);
            
            if (count($arr)) {
                $this->controllerName = array_pop($arr);
                $this->viewPath = strtolower($this->controllerName);
            }
        }
    }

    public function generateContent() {
        $contents = '';

        $contents = $this->getTempFile('controller.txt');
        $contents = str_replace("@@controllerName@@", $this->controllerName, $contents);
        $contents = str_replace("@@modelName@@", $this->modelName, $contents);
        $contents = str_replace("@@viewPath@@", $this->viewPath, $contents);
        //@@requestClass@@ @@table@@
        $requestClass = 'Request';
        $table = '';
        if (class_exists($this->modelName)) {
            $model = new $this->modelName;
            $table = $model->getTable();

            $requestName = $this->getModelName($table);
            $fullName = '\App\Http\Requests\\' . $requestName . 'Request';
            if (class_exists($fullName)) {
                $requestClass = $fullName;
            }
        }
        $contents = str_replace("@@requestClass@@", $requestClass, $contents);
        $contents = str_replace("@@table@@", $table, $contents);
        $contents = $this->checkRelation($table, $contents);

        return $contents;
    }

    public function make() {
        try {
            $controllerFileName = $this->controllerName . 'Controller.php';
            $fullPath = app_path('Http/Controllers/') . $controllerFileName;

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

    public function checkRelation($table, $contents) {
        $initialization = '';
        $variablePass = '';
        if (isset($this->finalRelationShips[$table])) {
            foreach ($this->finalRelationShips[$table] as $rel) {
                if ($rel['name'] == static::RELATION_BELONGS_TO) {
                    $initialization .= '$' . strtolower($rel['model']) . '=' . $rel['model'] . "::select(['id'])->get();" . "\n";
                    $variablePass.='"' . strtolower($rel['model']) . '"=>' . '$' . strtolower($rel['model']) . ',' . "\n";
                }
            }
        }
        $contents = str_replace("@@belongsToRelation@@", $initialization, $contents);
        $contents = str_replace("@@belongsToRelationVars@@", $variablePass, $contents);
        return $contents;
    }

}
