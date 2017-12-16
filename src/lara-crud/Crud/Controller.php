<?php
/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

namespace LaraCrud\Crud;


use DbReader\Table;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;

class Controller implements Crud
{
    use Helper;
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
    protected $table;

    /**
     * @var string
     */
    protected $fileName = '';

    /**
     * Sub Path of the Controller.
     * Generally Controller are stored in Controllers folder. But for grouping Controller may be put into folders.
     * @var type
     */
    public $path = '';

    /**
     * @var
     */
    public $namespace;

    /**
     * Import Namespace for Usages
     * @var array
     */
    public $import = [];

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
     * @var string
     */
    protected $template;

    /**
     * @var array
     */
    protected $only = ['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'];

    /**
     * ControllerCrud constructor.
     * @param $model
     * @param string $name
     * @param array|string $only
     * @param bool $api
     * @internal param array $except
     */
    public function __construct($model, $name = '', $only = '', $api = false)
    {
        $modelNamespace = config('laracrud.model.namespace', 'App');
        $this->shortModelName = $model;

        if (!empty($only) && is_array($only)) {
            $this->only = $only;
        }

        if (substr_compare($modelNamespace, "\\", 0, 1) !== 0) {
            $modelNamespace = "\\" . $modelNamespace;
        }
        $this->modelNameSpace = $modelNamespace;
        $this->requestClassSuffix = config('laracrud.request.classSuffix', 'Request');

        $this->modelName = $this->modelNameSpace . '\\' . $model;

        if (class_exists($this->modelName)) {
            $model = new $this->modelName;
            $this->table = $model->getTable();
        }

        if (!empty($name)) {
            if (strpos($name, "/") !== false) {
                $narr = explode("/", $name);
                $this->fileName = array_pop($narr);

                foreach ($narr as $p) {
                    $this->subNameSpace .= '\\' . $p;
                    $this->path .= '/' . $p;
                }
            } else {
                $this->fileName = $name;
            }
        }
        $this->template = !empty($api) ? 'api' : 'web';
        $ns = !empty($api) ? config('laracrud.controller.apiNamespace') : config('laracrud.controller.namespace');
        $this->namespace = trim($ns, "/") . $this->subNameSpace;
        $this->parseModelName();
    }

    /**
     * Process template and return complete code
     * @return mixed
     */
    public function template()
    {
        $globalVars = $this->globalVars();
        $methods = $this->buildMethods();
        $tempMan = new TemplateManager('controller/' . $this->template . '/template.txt', array_merge($globalVars, [
            'methods' => $methods,
            'importNameSpace' => $this->makeNamespaceUseString()
        ]));
        return $tempMan->get();
    }

    /**
     * @return array
     */
    protected function globalVars()
    {
        $rel = $this->makeRelation();
        return [
            'controllerName' => $this->getFileName($this->controllerName . 'Controller'),
            'modelName' => $this->shortModelName,
            'fullmodelName' => $this->modelName,
            'modelNameParam' => strtolower($this->shortModelName),
            'viewPath' => $this->viewPath,
            'requestClass' => $this->requestClass,
            'table' => $this->table,
            'namespace' => trim($this->namespace, "/"),
            'belongsToRelation' => $rel['belongsToRelation'],
            'belongsToRelationVars' => $rel['belongsToRelationVars'],
            'belongsToManyRelationSync' => '',
            'transformer' => '',
            'importNameSpace' => '',
        ];
    }

    /**
     * Get code and save to disk
     * @return mixed
     * @throws \Exception
     */
    public function save()
    {
        $this->checkPath("");
        $fileName = !empty($this->fileName) ? $this->getFileName($this->fileName) . ".php" : $this->controllerName . 'Controller' . '.php';
        $filePath = base_path($this->toPath($this->namespace)) . "/" . $fileName;
        if (file_exists($filePath)) {
            throw new \Exception($filePath . ' already exists');
        }
        $controller = new \SplFileObject($filePath, 'w+');
        $controller->fwrite($this->template());
    }

    /**
     * @return string
     */
    protected function buildMethods()
    {
        $retTemp = '';
        $tempMan = new TemplateManager('controller/' . $this->template . '/template.txt', []);
        foreach ($this->only as $method) {
            if ($filePath = $tempMan->getFullPath("controller/" . $this->template . '/' . $method . '.txt')) {
                $methodTemp = new TemplateManager("controller/" . $this->template . '/' . $method . ".txt", array_merge($this->globalVars(), [
                    'requestClass' => $this->getRequestClass($method)
                ]));
                $retTemp .= $methodTemp->get();
            }
        }
        return $retTemp;
    }

    /**
     * @param $method
     * @return string
     */
    protected function getRequestClass($method)
    {
        $api = $this->template == 'api' ? true : false;
        $requestFolder = !empty($this->table) ? ucfirst($this->table) : $this->modelName;
        $requestNs = !empty($api) ? config('laracrud.request.apiNamespace') : config('laracrud.request.namespace');
        $fullRequestNs = $requestNs . "\\" . $requestFolder . "\\" . ucfirst($method);

        if ($fullRequestNs) {
            $requestClass = ucfirst($method);
            $this->import[] = $fullRequestNs;
        } else {
            $requestClass = 'Request';
        }
        return $requestClass;
    }

    /**
     * Analyze Model and get extract information from there
     * Like Get folder Name of the view, Controller Short Name etc
     */
    protected function parseModelName()
    {
        $pagePath = config("laracrud.view.page.path");
        $class = new \ReflectionClass($this->modelName);
        $model = $class->newInstance();
        $this->modelNameSpace = $class->getNamespaceName();
        $this->viewPath = !empty($pagePath) ? str_replace("/", ".", $pagePath) . "." . $model->getTable() : $model->getTable();
        $this->controllerName = $class->getShortName();
    }

    /**
     * @return array
     */
    public function makeRelation()
    {
        $retArr = [
            'belongsToRelation' => '',
            'belongsToRelationVars' => ''
        ];
        if (!empty($this->table)) {
            $tableReader = new Table($this->table);
            $columnClasses = $tableReader->columnClasses();
            $rel = '';
            $relVars = '';
            foreach ($columnClasses as $column) {
                if ($column->isForeign()) {
                    $variableName = $column->foreignTable();
                    $this->import[] = $this->modelNameSpace . '\\' . $this->getModelName($variableName);
                    $rel .= "\t\t".'$' . strtolower($variableName) . ' = ' . $this->getModelName($variableName) . "::all(['id']);" . PHP_EOL;
                    $relVars .= "\t\t\t".'"' . strtolower($variableName) . '" => $' . strtolower($variableName) . ',' . PHP_EOL;
                }
            }
            $retArr['belongsToRelation'] = $rel;
            $retArr['belongsToRelationVars'] = $relVars;
        }
        return $retArr;
    }

    /**
     * @return string
     */
    protected function makeNamespaceUseString()
    {
        $retStr = '';
        $ns = array_unique($this->import);
        foreach ($ns as $namespace) {
            $retStr .= 'use ' . $namespace . ';' . PHP_EOL;
        }
        return $retStr;
    }
}