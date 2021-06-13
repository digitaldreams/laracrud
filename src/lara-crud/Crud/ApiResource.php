<?php


namespace LaraCrud\Crud;


use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;

class ApiResource implements Crud
{
    use Helper;

    private \Illuminate\Database\Eloquent\Model $model;
    private string $namespace;
    protected string $fileName;
    private string $subNameSpace;
    public $modelName;

    protected array $properties = [];

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(\Illuminate\Database\Eloquent\Model $model, ?string $name = null)
    {
        $this->model = $model;
        $this->fileName = $this->checkName($name);
        $this->namespace = $this->getFullNS(trim(config('laracrud.resource.namespace', 'App\Http\Resources'), ' / ')) . $this->subNameSpace;

    }

    public function template()
    {
        return (new TemplateManager('resource/template.txt', [
            'namespace' => $this->namespace,
            'className' => $this->fileName,
            'importNameSpace' => $this->makeNamespaceUseString(),
            'data' => implode("\n", $this->makeProperties()),
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
        $retStr = '';
        $hiddenArray = $this->model->getHidden();
        $fillableColumns = $this->model->getFillable();

        foreach ($fillableColumns as $column) {
            if (is_array($hiddenArray) && in_array($column, $hiddenArray)) {
                continue;
            }
            $this->properties[] .= '"' . $column . '" => $this>resource->' . $column->name() . ';';
        }

        return $this->properties;
    }


    /**
     * @param string|null $name
     *
     * @return string
     */
    private function checkName(?string $name = null): string
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
        }
    }

}
