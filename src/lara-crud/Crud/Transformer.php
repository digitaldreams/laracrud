<?php

namespace LaraCrud\Crud;

use DbReader\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;


class Transformer implements Crud
{
    use Helper;
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var string
     */
    protected $modelName;

    /**
     * parent namespace of the Transformer
     * @var string
     */
    protected $namespace;

    /**
     * @var \ReflectionClass
     */
    protected $reflectionClass;

    protected $availableIncludes = '';

    protected $includeArr = [];

    protected $includedModels = [];

    /**
     * Transformer constructor.
     * @param Model $model
     * @param bool $name
     * @throws \ReflectionException
     */
    public function __construct(Model $model, $name = false)
    {
        $this->model = $model;
        $this->name = $name;
        $this->namespace = $this->getFullNS(config('laracrud.transformer.namespace'));
        $this->reflectionClass = new \ReflectionClass(get_class($model));
        $this->modelName = !empty($name) ? $name : $this->reflectionClass->getShortName() . config('laracrud.transformer.classSuffix', 'Transformer');
        $this->makeIncludes();
    }

    /**
     * Process template and return complete code
     * @return mixed
     */
    public function template()
    {
        $vars = [
            'namespace' => $this->namespace,
            'modelFullName' => get_class($this->model),
            'model' => $this->reflectionClass->getShortName(),
            'properties' => $this->makeProperties(),
            'modelParam' => lcfirst($this->reflectionClass->getShortName()),
            'className' => $this->modelName,
            'availableInclude' => $this->availableIncludes,
            'defaultInclude' => '',
            'importNameSpace' => ''
        ];
        $vars['includes'] = $this->generateIncludeCode($vars);
        return (new TemplateManager('transformer/template.txt', $vars))->get();
    }

    /**
     * Get code and save to disk
     * @return mixed
     * @throws \Exception
     */
    public function save()
    {
        $filePath = $this->checkPath();
        if (file_exists($filePath)) {
            throw new \Exception('Transformer already exists');
        }
        $model = new \SplFileObject($filePath, 'w+');
        $model->fwrite($this->template());
    }

    private function makeProperties()
    {
        $retStr = '';
        $modelName = lcfirst($this->reflectionClass->getShortName());
        $table = $this->model->getTable();
        $tableLib = new Table($table);
        $hiddenArray = $this->model->getHidden();
        $columnClasses = $tableLib->columnClasses();
        foreach ($columnClasses as $columnClass) {
            if (is_array($hiddenArray) && in_array($columnClass->name(), $hiddenArray)) {
                continue;
            }
            $retStr .= "\t\t\t" . '"' . $columnClass->name() . '" => $' . $modelName . '->' . $columnClass->name() . "," . PHP_EOL;
        }
        return $retStr;
    }

    /**
     * @throws \ReflectionException
     */
    protected function makeIncludes()
    {
        $methods = $this->reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($method->getNumberOfParameters() == 0 && $method->class == get_class($this->model)) {
                $response = $method->invoke($this->model);
                if (!is_object($response)) {
                    continue;
                }
                $responseClass = get_class($response);
                if ($this->isItem($responseClass)) {
                    $this->makeIncludeArr('item', $method, $response);
                } elseif ($this->isCollection($responseClass)) {
                    $this->makeIncludeArr('collection', $method, $response);
                }
            }
        }
    }

    /**
     * @param $class
     * @return string
     * @throws \ReflectionException
     *
     */
    private function makeTransformer($class, $modelRef)
    {
        $shortName = $modelRef->getShortName();
        $transformerName = $shortName . config('laracrud.transformer.classSuffix');
        $transformerClass = $this->getFullNS(config('laracrud.transformer.namespace') . '\\' . $transformerName);
        $this->import[] = $transformerClass;
        return $transformerName;
    }


    protected function makeIncludeArr($response, $method, $relationResponse)
    {
        $class = $relationResponse->getQuery()->getModel();
        $this->includedModels[] = $class;
        $modelRef = new \ReflectionClass(get_class($class));
        $transformerClass = $this->makeTransformer($class, $modelRef);
        $this->availableIncludes .= '"' . strtolower($method->name) . '",';
        $this->includeArr[] = [
            'relation' => $method->name,
            'response' => $response,
            'method' => ucfirst($method->name),
            'includeTransformer' => $transformerClass
        ];
    }

    /**
     * @param $responseClass
     * @return bool
     */
    private function isItem($responseClass)
    {
        $item = [
            HasOne::class,
            BelongsTo::class,
            MorphOne::class
        ];
        return in_array($responseClass, $item);
    }

    /**
     * @param $responseClass
     * @return bool
     */
    private function isCollection($responseClass)
    {
        $collection = [
            HasMany::class,
            BelongsToMany::class,
            HasManyThrough::class,
            MorphMany::class,
        ];
        return in_array($responseClass, $collection);
    }

    /**
     * @param $vars
     * @return string
     */
    protected function generateIncludeCode($vars)
    {
        $temp = '';
        foreach ($this->includeArr as $inc) {
            $temp .= (new TemplateManager('transformer/include.txt', array_merge($vars, $inc)))->get();
        }
        return $temp;
    }

    /**
     * @return array
     */
    public function getIncludedModels()
    {
        return $this->includedModels;
    }

    public function getName()
    {
        return $this->modelName;
    }
}