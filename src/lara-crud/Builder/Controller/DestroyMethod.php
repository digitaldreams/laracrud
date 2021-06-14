<?php

namespace LaraCrud\Builder\Controller;

use LaraCrud\Builder\Controller\ControllerMethod;

abstract class DestroyMethod extends ControllerMethod
{
    /**
     * {@inheritdoc}
     */
    protected function beforeGenerate(): self
    {
        $this->setParentVariableAndParam()
            ->setParameter($this->getModelShortName(), '$' . $this->getModelVariableName());

        return $this;
    }

    /**
     * What code will be inside the destroy method.
     *
     * @return string
     */
    public function getBody(): string
    {
        return '$' . $this->getModelVariableName() . '->delete();';
    }

    /**
     * Redirect route Parameter.
     *
     * @return array
     */
    public function generateRouteParameter(): array
    {
        $parameters = parent::generateRouteParameter();
        unset($parameters[$this->getModelShortName()]);

        return $parameters;
    }


    public function phpDocComment(): string
    {
        return sprintf('Remove the specified %s from storage.', $this->getModelShortName());
    }
}
