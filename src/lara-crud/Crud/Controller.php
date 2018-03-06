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
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

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
     * @var string
     */
    protected $requestFolderNs = '';
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

    protected $transformerName;

    /**
     * @var bool|string
     */
    protected $parentModel;


    /**
     * ControllerCrud constructor.
     * @param $model
     * @param string $name
     * @param array|string $only
     * @param bool $api
     * @param bool|\Illuminate\Database\Eloquent\Model $parent
     * @internal param array $except
     * @throws \Exception
     */
    public function __construct($model, $name = '', $only = '', $api = false, $parent = false)
    {
        $modelNamespace = $this->getFullNS(config('laracrud.model.namespace', 'App'));

        $this->shortModelName = $model;

        if (!empty($only) && is_array($only)) {
            $this->only = $only;
        }

        $this->modelNameSpace = $modelNamespace;
        $this->requestClassSuffix = config('laracrud.request.classSuffix', 'Request');

        $this->modelName = $this->modelNameSpace . '\\' . $model;

        if (!empty($parent)) {
            $parentModel = $this->modelNameSpace . '\\' . $parent;
            if (!class_exists($parentModel)) {
                throw new \Exception($parent . ' class does not exists');
            }
            $this->import[] = $parentModel;
            $this->parentModel = $parent;
        }

        if (class_exists($this->modelName)) {
            $this->model = $model = new $this->modelName;
            $this->table = $model->getTable();
        } else {
            throw new \Exception($model . ' class does not exists');
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
        $this->template = !empty($this->parentModel) ? $this->template . '/parent' : $this->template;

        $ns = !empty($api) ? config('laracrud.controller.apiNamespace') : config('laracrud.controller.namespace');
        $this->namespace = trim($this->getFullNS($ns), "/") . $this->subNameSpace;
        $this->parseModelName();

        if (!empty($api)) {
            $this->transformerName = $this->getTransformerClass();
        }
        $requestNs = !empty($api) ? config('laracrud.request.apiNamespace') : config('laracrud.request.namespace');
        $requestFolder = !empty($this->table) ? ucfirst(camel_case($this->table)) : $this->modelName;
        $this->requestFolderNs = $this->getFullNS($requestNs) . "\\" . $requestFolder;
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
            'transformer' => $this->transformerName,
            'importNameSpace' => '',
            'parentModelName' => $this->parentModel,
            'parentModelNameParam' => strtolower($this->parentModel),
            'apiRequest' =>'{}',
            'apiResponse' => '{}'
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
                $requestClass = $this->getRequestClass($method);
                $methodTemp = new TemplateManager("controller/" . $this->template . '/' . $method . ".txt", array_merge($this->globalVars(), [
                    'requestClass' => $requestClass,
                    'apiRequest' => $this->makeApiRequest($requestClass)
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
        $fullRequestNs = $this->requestFolderNs . "\\" . ucfirst($method);
        if (class_exists($fullRequestNs)) {
            $requestClass = ucfirst($method);
            $this->import[] = $fullRequestNs;
        } else {
            $requestClass = 'Request';
        }
        return $requestClass;
    }

    /**
     * Get Transformer Class
     */
    protected function getTransformerClass()
    {
        $transformerNs = $this->getFullNS(config('laracrud.transformer.namespace', 'Transformers'));
        $suffiex = config('laracrud.transformer.classSuffix', 'Transformer');
        $transformerName = $this->shortModelName . $suffiex;
        $fullTransformerNs = $transformerNs . '\\' . $transformerName;
        $this->import[] = $fullTransformerNs;

        if (class_exists($fullTransformerNs)) {
            return $transformerName;
        } else if (is_object($this->model)) {
            $transformerCrud = new Transformer($this->model);
            $transformerCrud->save();
        }
        return $transformerName;
    }

    /**
     * @param $requestClass
     * @return array
     */
    protected function makeApiRequest($requestClass)
    {
        $rules = [];

        if (!class_exists($requestClass)) {
            $requestClass = $this->requestFolderNs . "\\" . $requestClass;
        }

        if (is_subclass_of($requestClass, \Dingo\Api\Http\FormRequest::class)) {
            $request = new $requestClass;
            $rules = $request->rules();
        }
        return !empty($rules) && is_array($rules) ? json_encode($rules, JSON_PRETTY_PRINT) : '{}';
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
                    $rel .= "\t\t" . '$' . strtolower($variableName) . ' = ' . $this->getModelName($variableName) . "::all(['id']);" . PHP_EOL;
                    $relVars .= "\t\t\t" . '"' . strtolower($variableName) . '" => $' . strtolower($variableName) . ',' . PHP_EOL;
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