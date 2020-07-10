<?php

namespace LaraCrud\Services\Controller;

use LaraCrud\Contracts\ViewAbleMethod;

class Show extends ControllerMethod implements ViewAbleMethod
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

        $this->setVariable(lcfirst($this->getModelShortName()), '$' . lcfirst($this->getModelShortName()))
            ->setParameter($this->getModelShortName(), '$' . lcfirst($this->getModelShortName()));

        return $this;
    }
}
