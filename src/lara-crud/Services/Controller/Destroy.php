<?php

namespace LaraCrud\Services\Controller;

use LaraCrud\Contracts\Controller\RedirectAbleMethod;

class Destroy extends ControllerMethod implements RedirectAbleMethod
{
    /**
     * @return \LaraCrud\Services\Controller\ControllerMethod|void
     * @throws \ReflectionException
     */
    protected function beforeGenerate()
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
    public function generateRouteParameter()
    {
        $parameters = parent::generateRouteParameter();
        unset($parameters[$this->getModelShortName()]);

        return $parameters;
    }
}
