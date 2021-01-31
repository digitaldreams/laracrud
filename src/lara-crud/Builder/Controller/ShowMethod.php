<?php

namespace LaraCrud\Builder\Controller;

use LaraCrud\Contracts\Controller\ViewAbleMethod;

class ShowMethod extends ControllerMethod implements ViewAbleMethod
{
    /**
     * {@inheritdoc}
     */
    protected function beforeGenerate(): self
    {
        $this->setParentVariableAndParam()
            ->setVariable(lcfirst($this->getModelShortName()), '$' . lcfirst($this->getModelShortName()))
            ->setParameter(ucfirst($this->getModelShortName()), '$' . lcfirst($this->getModelShortName()));

        return $this;
    }
}
