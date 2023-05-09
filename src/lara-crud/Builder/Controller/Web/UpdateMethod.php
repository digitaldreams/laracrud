<?php

namespace LaraCrud\Builder\Controller\Web;

use LaraCrud\Builder\Controller\ControllerMethod;
use LaraCrud\Contracts\Controller\RedirectAbleMethod;

class UpdateMethod extends ControllerMethod implements RedirectAbleMethod
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
