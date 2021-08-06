<?php

namespace LaraCrud\Crud;

use Illuminate\Database\Eloquent\Model;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\Repositories\ControllerRepository;

class Controller implements Crud
{
    use Helper;

    /**
     * Controller Name prefix.
     * If Model Name is User and no controller name is supplier then it will be User and then Controller will be
     * appended. Its name will be UserController.
     *
     * @var string
     */
    protected string $controllerName;

    /**
     * Model Name.
     *
     * @var string
     */
    protected string $modelName;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var string
     */
    protected string $fileName = '';

    /**
     * Sub Path of the Controller.
     * Generally Controller are stored in Controllers folder. But for grouping Controller may be put into folders.
     *
     * @var string
     */
    public string $path = '';

    /**
     * @var string
     */
    public string $namespace;

    /**
     * Namespace version of subpath.
     *
     * @var string
     */
    protected string $subNameSpace = '';

    /**
     * @var bool|string
     */
    protected $parentModel;

    /**
     * @var \LaraCrud\Repositories\ControllerRepository
     */
    protected ControllerRepository $controllerRepository;

    /**
     * ControllerCrud constructor.
     *
     * @param \LaraCrud\Repositories\ControllerRepository $controllerRepository
     * @param \Illuminate\Database\Eloquent\Model         $model
     * @param string|null                                 $controllerFileName
     * @param bool                                        $api
     *
     * @internal param array $except
     */
    public function __construct(
        ControllerRepository $controllerRepository,
        Model $model,
        ?string $controllerFileName = '',
        bool $api = false
    ) {
        $this->model = $model;
        $this->resolveControllerFileName($controllerFileName);

        $ns = ! empty($api) ? config('laracrud.controller.apiNamespace') : config('laracrud.controller.namespace');
        $this->namespace = trim($this->getFullNS($ns), '/') . $this->subNameSpace;
        $this->controllerRepository = $controllerRepository;
    }

    /**
     * Generate full code and return as string.
     *
     * @return string
     */
    public function template(): string
    {
        $modelShortName = (new \ReflectionClass($this->model))->getShortName();
        $this->controllerRepository->build();
        $tempMan = new TemplateManager('controller/template.txt', [
            'namespace' => $this->namespace,
            'fullmodelName' => get_class($this->model),
            'controllerName' => $this->fileName,
            'methods' => implode("\n", $this->controllerRepository->getCode()),
            'importNameSpace' => $this->makeNamespaceImportString(),
            'modelVariable' => lcfirst($modelShortName),
            'model' => $modelShortName,
        ]);

        return $tempMan->get();
    }

    /**
     * Get code and save to disk.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function save()
    {
        $this->checkPath('');
        $fileName = ! empty($this->fileName) ? $this->getFileName($this->fileName) . '.php' : $this->controllerName . 'Controller' . '.php';
        $filePath = base_path($this->toPath($this->namespace)) . '/' . $fileName;
        if (file_exists($filePath)) {
            throw new \Exception($filePath . ' already exists');
        }
        $controller = new \SplFileObject($filePath, 'w+');
        $controller->fwrite($this->template());
    }

    /**
     * Get full newly created fully qualified Class namespace.
     */
    public function getFullName()
    {
        $fileName = ! empty($this->fileName) ? $this->getFileName($this->fileName) : $this->controllerName . 'Controller';

        return $this->namespace . '\\' . $fileName;
    }

    /**
     * @param string $name
     *
     * @return \LaraCrud\Crud\Controller
     */
    public function resolveControllerFileName(?string $name=null): self
    {
        if (! empty($name)) {
            if (false !== strpos($name, '/')) {
                $narr = explode('/', $name);
                $this->modelName = $this->fileName = array_pop($narr);

                foreach ($narr as $p) {
                    $this->subNameSpace .= '\\' . $p;
                    $this->path .= '/' . $p;
                }
            } else {
                $this->modelName = $this->fileName = $name;
            }
        } else {
            // Controller Name is empty, Lets create a new name from Model Name like PostController.
            $controllerNamePrefix = config('laracrud.controller.classSuffix');
            $this->modelName = $this->fileName = (new \ReflectionClass($this->model))->getShortName() . $controllerNamePrefix;
        }

        return $this;
    }


    public function makeNamespaceImportString()
    {
        $ns = '';
        foreach ($this->controllerRepository->getImportableNamespaces() as $namespace) {
            $ns .= "\n use " . $namespace . ';';
        }

        return $ns;
    }

}
