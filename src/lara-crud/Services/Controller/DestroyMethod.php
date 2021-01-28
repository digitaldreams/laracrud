<?php

namespace LaraCrud\Services\Controller;

use LaraCrud\Contracts\Controller\RedirectAbleMethod;

class DestroyMethod extends ControllerMethod implements RedirectAbleMethod
{
    /**
     * {@inheritdoc}
     */
    protected function beforeGenerate(): self
    {
        if ($this->parentModel) {
            $this->setParameter(ucfirst($this->getParentShortName()), '$' . $this->getParentShortName());
        }
        $this->setParameter(ucfirst($this->getModelShortName()), '$' . $this->getModelShortName());

        return $this;
    }

    /**
     * @return string
     */
    public function redirectToRouteMethodName(): string
    {
        return 'index';
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return '$' . $this->getModelShortName() . '->delete();';
    }

    /**
     * @return array
     */
    public function generateRouteParameter(): array
    {
        $parameters = parent::generateRouteParameter();
        unset($parameters[$this->getModelShortName()]);

        return $parameters;
    }
}
