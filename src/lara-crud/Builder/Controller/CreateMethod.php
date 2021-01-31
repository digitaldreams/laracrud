<?php

namespace LaraCrud\Builder\Controller;

use LaraCrud\Contracts\Controller\ViewAbleMethod;

class CreateMethod extends ControllerMethod implements ViewAbleMethod
{
    /**
     * {@inheritdoc}
     */
    protected function beforeGenerate(): self
    {

        $this->setParentVariableAndParam()
            ->setVariable($this->getModelVariableName(), 'new ' . $this->getModelShortName());

        return $this;
    }
}
