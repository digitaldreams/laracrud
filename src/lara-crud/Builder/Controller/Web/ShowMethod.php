<?php

namespace LaraCrud\Builder\Controller\Web;

use LaraCrud\Builder\Controller\ControllerMethod;
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


    public function phpDocComment(): string
    {
        return sprintf('Display the specified %s.', $this->getModelShortName());
    }
}
