<?php

namespace LaraCrud\Crud;

use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\ClassInspector;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\Services\ModelRelationReader;

class Policy implements Crud
{
    use Helper;

    /**
     * Controller Name prefix.
     * If Model Name is User and no controller name is supplier then it will be User and then Controller will be
     * appended. Its name will be UserController.
     *
     * @var string
     */
    protected $controllerName;

    /**
     * Model Name.
     *
     * @var string
     */
    protected $modelName;

    /**
     * Default Model Namespace. So if not namespace is specified on
     *  Model then this namespace will be added and check if model exists.
     *
     * @var string
     */
    protected $modelNameSpace = 'App';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var
     */
    public $namespace;

    /**
     * Namespace version of subpath.
     *
     * @var string
     */
    protected $subNameSpace = '';

    /**
     * @var array
     */
    protected $only = ['index', 'show', 'create', 'update', 'destroy'];

    /**
     * Model Name without Namespace.
     *
     * @var string
     */
    protected $shortModelName;

    /**
     * @var string
     */
    protected $modelFullClass;

    protected $modelRelationReader;

    /**
     * Policy constructor.
     *
     * @param        $model
     * @param string $controller
     * @param string $name
     * @param string $only
     *
     * @throws \Exception
     */
    public function __construct(string $model, ?string $controller = null, ?string $name = null, array $only = [])
    {
        $this->modelFullClass = $model;
        $this->modelRelationReader = (new ModelRelationReader(new $this->modelFullClass()))->read();
        $this->shortModelName = $this->modelRelationReader->getShortName();
        $this->checkController($controller);

        if (! empty($only) && is_array($only)) {
            $this->only = $only;
        }


        $this->checkName($name);
        $this->namespace = $this->getFullNS(trim((string) config('laracrud.policy.namespace'), ' / ')) . $this->subNameSpace;
    }

    /**
     * Process template and return complete code.
     *
     * @return mixed
     */
    public function template()
    {
        $methodsTemp = [];
        $tempCheck = new TemplateManager('policy/template.txt');
        foreach ($this->only as $method) {
            $fileName = $tempCheck->getFullPath("policy/$method.txt") ? "policy/$method.txt" : 'policy/default.txt';
            $methodsTemp[] = (new TemplateManager($fileName, [
                'method' => $method,
                'modelClass' => $this->shortModelName,
                'modelFullClass' => $this->modelFullClass,
                'modelClassVar' => lcfirst($this->shortModelName),
                'return' => $this->getReturnString(),
            ]))->get();
        }
        $userClass = ! empty(config('auth.providers.users.model')) ? config('auth.providers.users.model') : config('laracrud.model.namespace') . '\\User';

        return (new TemplateManager('policy/template.txt', [
            'namespace' => $this->namespace,
            'className' => $this->getClassName(),
            'modelFullClass' => $this->modelFullClass,
            'userClass' => $userClass,
            'methods' => implode("\n", $methodsTemp),
        ]))->get();
    }

    /**
     * Get code and save to disk.
     *
     * @return mixed
     */
    public function save()
    {
        $this->modelName = $this->getClassName();
        $filePath = $this->checkPath();
        $file = new \SplFileObject($filePath, 'w+');
        $file->fwrite($this->template());
        $file->fflush();
    }

    /**
     * @param $name
     */
    private function checkName($name)
    {
        if (! empty($name)) {
            if (str_contains((string) $name, ' / ')) {
                $narr = explode(' / ', (string) $name);
                $this->name = array_pop($narr);

                foreach ($narr as $p) {
                    $this->subNameSpace .= '\\' . $p;
                }
            } else {
                $this->name = $name;
            }
        }
    }

    /**
     * @param $controller
     *
     * @throws \Exception
     */
    private function checkController($controller)
    {
        if (! empty($controller)) {
            $this->controllerName = class_exists($controller) ? $controller : $this->getFullNS(config('laracrud.controller.namespace') . '\\' . $controller);
            if (! class_exists($this->controllerName)) {
                throw new \Exception($controller . ' does not exists');
            }
            $classInspector = new ClassInspector($this->controllerName);
            $this->only = $classInspector->publicMethods;
        }
    }

    /**
     * @return type|string
     */
    private function getClassName()
    {
        return ! empty($this->name) ? $this->name : $this->shortModelName . config('laracrud.policy.classSuffix');
    }

    protected function getReturnString(): string
    {
        if ($this->modelRelationReader->hasOwner()) {
            $modelVar = '$' . lcfirst($this->shortModelName . '->' . $this->modelRelationReader->getOwnerForeignKey());

            return '$user->' . $this->modelRelationReader->getOwnerLocalKey() . '===' . $modelVar;
        }

        return 'true';
    }
}
