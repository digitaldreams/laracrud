<?php

namespace LaraCrud\Services\Controller;

use LaraCrud\Contracts\ViewAbleMethod;

class Create extends ControllerMethod implements ViewAbleMethod
{
    /**
     * Set necessary data.
     *
     * @return $this
     *
     * @throws \ReflectionException
     */
    protected function beforeGenerate()
    {
        if ($this->parentModel) {
            $this->setVariable(lcfirst($this->getParentShortName()), '$' . lcfirst($this->getParentShortName()))
                ->setParameter($this->getParentShortName(), '$' . lcfirst($this->getParentShortName()));
        }

        $this->setVariable(lcfirst($this->getModelShortName()), 'new ' . $this->getModelShortName());

        return $this;
    }
}
