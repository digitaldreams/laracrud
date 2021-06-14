<?php

namespace LaraCrud\Builder\Controller;

use LaraCrud\Builder\Controller\ControllerMethod;

abstract class UpdateMethod extends ControllerMethod
{
    /**
     * {@inheritdoc}
     */
    protected function beforeGenerate(): self
    {
        $requestClass = $this->getRequestClass();
        $this->setParameter($requestClass, '$request');

        $this->setParentVariableAndParam()->setParameter($this->getModelShortName(), '$' . $this->getModelVariableName());

        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        $variable = '$' . $this->getModelVariableName();

        return $variable . '->fill($request->all())->save();' . PHP_EOL;
    }


    public function phpDocComment(): string
    {
        return sprintf('Update the specified %s in storage.', $this->getModelShortName());
    }
}
