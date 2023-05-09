<?php

namespace LaraCrud\Helpers;

use Illuminate\Support\Str;
use LaraCrud\Builder\Controller\ControllerMethod;
use LaraCrud\Contracts\Controller\ViewAbleMethod;

trait ViewAbleMethodHelper
{
    /**
     * blade file path for view method.
     *
     * @var string|null
     */
    protected $viewFilePath = null;

    /**
     * List of key value pair that will be used as view data.
     *
     * @var array
     */
    protected array $variables = [];

    /**
     * List of key value class pair that will be used to as method parameters.
     *
     * @var array
     */
    protected array $parameters = [];

    /**
     * @param $filePath
     *
     * @return $this
     */
    public function setViewFilePath($filePath): ControllerMethod
    {
        $this->viewFilePath = $filePath;

        return $this;
    }

    /**
     * @return string|null
     *
     * @throws \ReflectionException
     */
    public function getViewFilePath(): string
    {
        if (!empty($this->viewFilePath)) {
            return $this->viewFilePath;
        }

        if ($this instanceof ViewAbleMethod) {
            $viewNs = config('laracrud.view.namespace') ? rtrim((string) config('laracrud.view.namespace'), '::') . '::' : '';

            return $this->viewFilePath = $viewNs . 'pages.' . Str::plural(lcfirst($this->getModelShortName())) . '.' . $this->getMethodName();
        }

        return $this->viewFilePath;
    }

    public function getVariables(): array
    {
        $data = [];

        if ($this->parentModel) {
            $parentModelName = $this->getParentShortName();
            $data['$' . $parentModelName] = $parentModelName;
        }
        $modelName = $this->getModelShortName();
        $data['$' . $modelName] = $modelName;

        return $data;
    }

    /**
     * @throws \ReflectionException
     */
    protected function generateViewCode(): string
    {
        return (new TemplateManager('controller/web/view.txt', [
            'variables' => $this->buildVariables(),
            'parameters' => $this->buildParameters(),
            'body' => $this->getBody(),
            'methodName' => $this->getMethodName(),
            'viewPath' => $this->getViewFilePath(),
            'PHPDocComment' => $this->phpDocComment(),
            'authorization' => $this->getAuthorization(),
            'modelVariable' => $this->getModelVariableName(),
            'model' => $this->getModelShortName(),
            'parentModelVariable' => $this->getParentVariableName(),
            'parentModel' => $this->getParentShortName(),
        ]))->get();
    }

    protected function buildVariables(array $variables = []): string
    {
        $variables = !empty($variables) ? $variables : $this->variables;

        $dataString = PHP_EOL . '';
        foreach ($variables as $key => $variable) {
            $dataString .= "\t\t'" . $key . "' => " . $variable . ',' . PHP_EOL;
        }

        return $dataString;
    }

    public function buildParameters(array $parameters = []): string
    {
        $parameterString = '';
        $parameters = !empty($parameters) ? $parameters : $this->parameters;

        foreach ($parameters as $class => $variable) {
            $parameterString .= $class . ' ' . $variable . ',';
        }

        return trim($parameterString, ',');
    }

    /**
     *
     * @return $this
     */
    public function setVariable(string $key, string $variable): self
    {
        $this->variables[$key] = $variable;

        return $this;
    }

    /**
     *
     * @return $this
     */
    public function setParameter(string $class, string $variable): self
    {
        $this->parameters[$class] = $variable;

        return $this;
    }
}
