<?php

namespace LaraCrud\Crud;

use LaraCrud\Contracts\Crud;
use LaraCrud\Contracts\TableContract;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;

class ApiResource implements Crud
{
    use Helper;

    private \Illuminate\Database\Eloquent\Model $model;
    private string $namespace;
    protected string $fileName;
    private $subNameSpace;
    public $modelName;

    protected array $properties = [];

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(\Illuminate\Database\Eloquent\Model $model, ?string $name = null)
    {
        $this->model = $model;
        $this->checkName($name);
        $this->namespace = $this->getFullNS(trim(config('laracrud.resource.namespace', 'App\Http\Resources'), ' / ')) . $this->subNameSpace;
    }

    public function template()
    {
        return (new TemplateManager('resource/template.txt', [
            'namespace' => $this->namespace,
            'className' => $this->fileName,
            'importNameSpace' => $this->makeNamespaceUseString(),
            'data' => implode("\n", $this->createRelatedResourceClass()->makeProperties()),
        ]))->get();
    }

    public function save()
    {
        $this->modelName = $this->fileName;
        $filePath = $this->checkPath();
        $file = new \SplFileObject($filePath, 'w+');
        $file->fwrite($this->template());
        $file->fflush();
    }

    public function makeProperties(): array
    {
        $tableRepository = app()->make(TableContract::class, ['table' => $this->model->getTable()]);

        foreach ($tableRepository->columns() as $columnRepository) {
            if (!$columnRepository->isForeign()) {
                $this->properties[] = "\t\t\t".'"' . $columnRepository->name() . '" => $this->resource->' . $columnRepository->name() . ',';
            }
        }

        return $this->properties;
    }

    /**
     * @return $this
     */
    public function createRelatedResourceClass(): self
    {
        return $this;
    }


    /**
     * @param string|null $name
     *
     * @return string
     */
    private function checkName(?string $name = null)
    {
        if (!empty($name)) {
            if (false !== strpos($name, ' / ')) {
                $narr = explode(' / ', $name);
                $this->fileName = array_pop($narr);

                foreach ($narr as $p) {
                    $this->subNameSpace .= '\\' . $p;
                }
            } else {
                $this->fileName = $name;
            }
        } else {
            $this->fileName = (new \ReflectionClass($this->model))->getShortName() . 'Resource';
        }
    }

}
