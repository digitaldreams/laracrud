<?php

namespace LaraCrud\Services\Controller;

use LaraCrud\Contracts\Controller\ViewAbleMethod;

class ShowMethod extends ControllerMethod implements ViewAbleMethod
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

        $this->setVariable(lcfirst($this->getModelShortName()), '$' . lcfirst($this->getModelShortName()))
            ->setParameter($this->getModelShortName(), '$' . lcfirst($this->getModelShortName()));

        return $this;
    }
}
