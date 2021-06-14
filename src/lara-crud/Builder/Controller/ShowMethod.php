<?php

namespace LaraCrud\Builder\Controller;

use LaraCrud\Builder\Controller\ControllerMethod;

abstract class ShowMethod extends ControllerMethod
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


    public function phpDocComment(): string
    {
        return sprintf('Display the specified %s.', $this->getModelShortName());
    }
}
