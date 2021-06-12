<?php

namespace LaraCrud\Builder\Controller\Web;

use LaraCrud\Builder\Controller\ControllerMethod;
use LaraCrud\Contracts\Controller\RedirectAbleMethod;

class DestroyMethod extends ControllerMethod implements RedirectAbleMethod
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
     * Name of the Route user will be redirected after successfully Delete.
     *
     * @return string
     */
    public function redirectToRouteMethodName(): string
    {
        return 'index';
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
