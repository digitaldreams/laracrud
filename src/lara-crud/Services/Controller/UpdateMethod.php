<?php

namespace LaraCrud\Services\Controller;

use LaraCrud\Contracts\Controller\RedirectAbleMethod;

class UpdateMethod extends ControllerMethod implements RedirectAbleMethod
{
    /**
     * {@inheritdoc}
     */
    protected function beforeGenerate(): self
    {
        $requestClass = $this->getRequestClass();
        $this->setParameter($requestClass, '$request');

        if ($this->parentModel) {
            $this->setParameter(ucfirst($this->getParentShortName()), '$' . $this->getParentShortName());
        }
        $this->setParameter(ucfirst($this->getModelShortName()), '$' . $this->getModelShortName());

        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        $variable = '$' . $this->getModelShortName();

        return $variable . '->fill($request->all())->save();' . PHP_EOL;
    }
}
