<?php

namespace LaraCrud\Generators;

use Illuminate\Database\Eloquent\Model;
use LaraCrud\Contracts\ClassGeneratorContract;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\NamespaceResolver;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\Repositories\ControllerRepository;

class Controller implements ClassGeneratorContract
{
    use Helper, FileSave;

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

    protected Model $model;

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
        protected ControllerRepository $controllerRepository,
        Model $model,
        ?string $controllerFileName = '',
        bool $api = false
    ) {
        $this->model = $model;
        $this->resolveControllerFileName($controllerFileName);

        $ns = !empty($api) ? config('laracrud.controller.apiNamespace') : config('laracrud.controller.namespace');
        $this->namespace = trim(NamespaceResolver::getFullNS($ns), '/') . $this->subNameSpace;
    }

    /**
     * Generate full code and return as string.
     */
    public function template(): string
    {
        $modelShortName = (new \ReflectionClass($this->model))->getShortName();
        $this->controllerRepository->build();
        $tempMan = new TemplateManager('controller/template.txt', [
            'namespace' => $this->namespace,
            'fullmodelName' => $this->model::class,
            'controllerName' => $this->fileName,
            'methods' => implode("\n", $this->controllerRepository->getCode()),
            'importNameSpace' => $this->makeNamespaceImportString(),
            'modelVariable' => lcfirst($modelShortName),
            'model' => $modelShortName,
        ]);

        return $tempMan->get();
    }

    public function resolveControllerFileName(?string $name = null): self
    {
        if (!empty($name)) {
            if (str_contains($name, '/')) {
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
            $this->modelName = $this->fileName = (new \ReflectionClass($this->model))->getShortName(
                ) . $controllerNamePrefix;
        }

        return $this;
    }


    public function makeNamespaceImportString(): string
    {
        $ns = '';
        foreach ($this->controllerRepository->getImportableNamespaces() as $namespace) {
            $ns .= "\n use " . $namespace . ';';
        }

        return $ns;
    }

    public function getClassName(): string
    {
        return !empty($this->fileName) ? $this->getFileName(
            $this->fileName
        ) : $this->controllerName . 'Controller';
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
