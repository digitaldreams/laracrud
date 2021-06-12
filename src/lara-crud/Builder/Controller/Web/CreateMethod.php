<?php

namespace LaraCrud\Builder\Controller\Web;

use LaraCrud\Builder\Controller\ControllerMethod;
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

    public function phpDocComment(): string
    {
        return sprintf('Show the form for creating a new %s.', $this->getModelShortName());
    }
}
