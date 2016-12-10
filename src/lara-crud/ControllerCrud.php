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
    /**
     * Controller Name prefix.
     * If Model Name is User and no controller name is supplier then it will be User and then Controller will be appended.
     * Its name will be UserController

     * @var string
     */
    protected $controllerName;

    /**
     * Model Name 
     *
     * @var string
     */
    protected $modelName;

    /**
     * View Path of the Controller.
     * This will be lower case of model name.
     * @var type 
     */
    protected $viewPath;

    /**
     * Default Model Namespace. So if not namespace is specified on
     *  Model then this namespace will be added and check if model exists.
     * @var type
     */
    protected $modelNameSpace = 'App';

    /**
     * Request Class.
     * Check if any Request Class created for this Model. If so then Use that Request Name otherwise use default Request

     * @var type 
     */
    protected $requestClass = 'Request';

    /**
     * Generally all Request class are suffix with Request.
     * So for Model User it will search UserRequest in Request folder
     * @var string
     */
    protected $requestClassSuffix = 'Request';

    /**
     * Name of the Model Table
     * @var string
     */
    public $table;
    protected $fileName = '';

    /**
     * Sub Path of the Controller.
     * Generally Controller are stored in Controllers folder. But for grouping Controller may be put into folders.
     * @var type 
     */
    public $path = '';

    /**
     * Namespace version of subpath
     * @var type 
     */
    protected $subNameSpace = '';

    /**
     * Model Name without Namespace
     * @var type
     */
    protected $shortModelName;

    /**
     *
     * @param String $modelName Model Name
     * @param String $name Controller Name. Optional if not specfied Model Name will be used
     */
    public function __construct($modelName = '', $name = '')
    {
        parent::__construct();
        $modelNamespace       = $this->getConfig('modelNameSpace', 'App');
        $this->shortModelName = $modelName;

        if (substr_compare($modelNamespace, "\\", 0, 1) !== 0) {
            $modelNamespace = "\\".$modelNamespace;
        }
        $this->modelNameSpace     = $modelNamespace;
        $this->requestClassSuffix = $this->getConfig('requestClassSuffix', 'Request');

        $this->modelName = $this->modelNameSpace.'\\'.$modelName;
        if (!empty($name)) {
            if (strpos($name, "/") !== false) {
                $narr           = explode("/", $name);
                $this->fileName = array_pop($narr);

                foreach ($narr as $p) {
                    $this->subNameSpace.='\\'.$p;
                    $this->path.='/'.$p;
                }
            } else {
                $this->fileName = $name;
            }
        }

        // $this->getTableList();
        //$this->loadDetails();
        $this->init();
        $this->prepareRelation();
    }

    /**
     * Preparing Info like Table Name, RequestName, Path and Sub Namespace based on Model  and Controller Name
     */
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

                $requestPath = $this->getConfig("requestPath", 'app/Http/Requests');

                $fullName = $this->pathToNs($requestPath).''.$requestName.$this->requestClassSuffix;
                if (class_exists($fullName)) {
                    $this->requestClass = $fullName;
                }
            }
        }
    }

    /**
     * Analyze Model and get extract information from there
     * Like Get folder Name of the view, Controller Short Name etc
     */
    protected function parseModelName()
    {
        $class                = new \ReflectionClass($this->modelName);
        $this->modelNameSpace = $class->getNamespaceName();
        $this->viewPath       = strtolower($class->getShortName());
        $this->controllerName = $class->getShortName();
    }

    /**
     * Make Controller Code
     * @return string PHP code
     */
    public function generateContent()
    {
        $contents = '';

        $contents = $this->getTempFile('controller.txt');
        $contents = str_replace("@@controllerName@@", $this->getFileName($this->controllerName.'Controller'), $contents);
        $contents = str_replace("@@modelName@@", $this->shortModelName, $contents);
        $contents = str_replace("@@fullmodelName@@", $this->modelName, $contents);
        $contents = str_replace("@@modelNameParam@@", strtolower($this->shortModelName), $contents);
        $contents = str_replace("@@viewPath@@", $this->viewPath, $contents);


        $contents        = str_replace("@@requestClass@@", $this->requestClass, $contents);
        $contents        = str_replace("@@table@@", $this->table, $contents);
        $parentNameSpace = $this->getConfig('controllerNameSpace', 'App\Http\Controllers');
        $contents        = str_replace("@@namespace@@", $parentNameSpace.$this->subNameSpace, $contents);

        $contents = $this->checkRelation($this->table, $contents);

        return $contents;
    }

    /**
     * Make the Controller.
     * 
     * @return boolean
     * @throws \Exception
     */
    public function make()
    {
        try {
            $controllerFileName = $this->getFileName($this->controllerName.'Controller').'.php';
            $fullPath           = base_path($this->getConfig('controllerPath', 'app/Http/Controllers/'));

            if (!empty($this->path)) {
                $fullPath.=$this->path.'/';

                if (!file_exists($fullPath)) {
                    mkdir($fullPath);
                }
            }
            $fullPath.=$controllerFileName;

            if (!file_exists($fullPath)) {
                $modelContent = $this->generateContent();
                $this->saveFile($fullPath, $modelContent);
                return true;
            } else {
                throw new \Exception('Controller already exists');
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode(), $ex);
        }
        return false;
    }

    /**
     * Check the Relation and if model has any Many To Many then run sync after the create of model
     * @param string $table
     * @param string $contents
     * @return string
     */
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
        $contents = str_replace("@@belongsToRelation@@", $initialization, $contents);
        $contents = str_replace("@@belongsToRelationVars@@", $variablePass, $contents);
        $contents = str_replace("@@belongsToManyRelationSync@@", $bmanySync, $contents);
        return $contents;
    }

    /**
     * Get Controller File and Class Name
     * @param string $name Default Name. It will be used if user does not provide any name.
     * @return type
     */
    public function getFileName($name)
    {
        if (!empty($this->fileName)) {
            return str_replace(".php", "", $this->fileName);
        }
        return $name;
    }
}