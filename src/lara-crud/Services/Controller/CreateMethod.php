<?php

namespace LaraCrud\Services\Controller;

use LaraCrud\Contracts\Controller\ViewAbleMethod;

class CreateMethod extends ControllerMethod implements ViewAbleMethod
{
    /**
     * {@inheritdoc}
     */
    protected function beforeGenerate(): self
    {
        if ($this->parentModel) {
            $this->setVariable(lcfirst($this->getParentShortName()), '$' . lcfirst($this->getParentShortName()))
                ->setParameter($this->getParentShortName(), '$' . lcfirst($this->getParentShortName()));
        }

        $this->setVariable(lcfirst($this->getModelShortName()), 'new ' . $this->getModelShortName());

        return $this;
    }
}
