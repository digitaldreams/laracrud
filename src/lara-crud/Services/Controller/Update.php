<?php

namespace LaraCrud\Services\Controller;

use LaraCrud\Contracts\Controller\RedirectAbleMethod;

class Update extends ControllerMethod implements RedirectAbleMethod
{
    /**
     * @throws \ReflectionException
     *
     * @return \LaraCrud\Services\Controller\ControllerMethod|void
     */
    protected function beforeGenerate()
    {
        $requestClass = $this->getRequestClass();
        $this->setParameter($requestClass, '$request');

        if ($this->parentModel) {
            $this->setParameter(ucfirst($this->getParentShortName()), '$'.$this->getParentShortName());
        }
        $this->setParameter(ucfirst($this->getModelShortName()), '$'.$this->getModelShortName());

        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        $variable = '$'.$this->getModelShortName();

        return $variable.'->fill($request->all())->save();'.PHP_EOL;
    }
}
