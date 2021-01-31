<?php


namespace LaraCrud\Builder\Controller;


class ForceDeleteMethod extends RestoreMethod
{
    /**
     * @return string
     */
    public function getBody(): string
    {
        $variable = '$' . $this->getModelVariableName();
        $body = $variable . ' = ' . $this->getModelShortName() . '::withTrashed()->where(\'' . $this->model->getRouteKeyName() . '\',' . $variable . ')->firstOrFail()' . PHP_EOL;

        $body .= "\t\t" . $variable . '->forceDelete();' . PHP_EOL;

        return $body;
    }

    /**
     * @return array
     */
    public function generateRouteParameter(): array
    {
        $parameters = parent::generateRouteParameter();
        unset($parameters[$this->getModelShortName()]);

        return $parameters;
    }

    /**
     * @return string
     */
    public function redirectToRouteMethodName(): string
    {
        return 'index';
    }

}
