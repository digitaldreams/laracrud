<?php

namespace LaraCrud\Helpers;

trait ApiMethodHelper
{
    public function generateApiArrayCode(): string
    {
        return (new TemplateManager('controller/api/array.txt', [
            'parameters' => $this->buildParameters(),
            'body' => $this->getBody(),
            'methodName' => $this->getMethodName(),
            'PHPDocComment' => $this->phpDocComment(),
            'authorization' => $this->getAuthorization(),
            'data' => $this->arrayToString($this->array()),
            'modelVariable' => $this->getModelVariableName(),
            'model' => $this->getModelShortName(),
            'parentModelVariable' => $this->getParentVariableName(),
            'parentModel' => $this->getParentShortName(),
        ]))->get();
    }

    public function generateApiResourceCode(): string
    {
        return (new TemplateManager('controller/api/resource.txt', [
            'parameters' => $this->buildParameters(),
            'body' => $this->getBody(),
            'methodName' => $this->getMethodName(),
            'PHPDocComment' => $this->phpDocComment(),
            'authorization' => $this->getAuthorization(),
            'response' => $this->generateResourceResponse(),
            'modelVariable' => $this->getModelVariableName(),
            'model' => $this->getModelShortName(),
            'parentModelVariable' => $this->getParentVariableName(),
            'parentModel' => $this->getParentShortName(),
        ]))->get();
    }

    public function arrayToString(array $data)
    {
        $str = "\n";
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $str .= "\t\t\t" . '"' . $key . '" => ' . (bool) $value . ',' . PHP_EOL;
            } else {
                $str .= "\t\t\t" . '"' . $key . '" => "' . $value . '",' . PHP_EOL;
            }
        }

        return $str;
    }
}
