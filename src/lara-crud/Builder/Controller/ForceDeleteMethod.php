<?php

namespace LaraCrud\Builder\Controller;

abstract class ForceDeleteMethod extends RestoreMethod
{
    public function getBody(): string
    {
        $variable = '$' . $this->getModelVariableName();
        $body = $variable . ' = ' . $this->getModelShortName() . '::withTrashed()->where(\'' . $this->model->getRouteKeyName() . '\',' . $variable . ')->firstOrFail();' . PHP_EOL;

        $body .= "\t\t" . $variable . '->forceDelete();' . PHP_EOL;

        return $body;
    }

    public function generateRouteParameter(): array
    {
        $parameters = parent::generateRouteParameter();
        unset($parameters[$this->getModelShortName()]);

        return $parameters;
    }

    public function phpDocComment(): string
    {
        return sprintf('Remove the specified %s from the bin permanently.', $this->getModelShortName());
    }
}
