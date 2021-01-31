<?php

namespace LaraCrud\Builder\Controller;

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

    /**
     * @return string
     */
    public function getBody(): string
    {
        $variable = '$' . $this->getModelVariableName();

        return $variable . '->fill($request->all())->save();' . PHP_EOL;
    }
}
