<?php

namespace LaraCrud\Services\Controller;

use LaraCrud\Contracts\Controller\RedirectAbleMethod;

class Restore extends ControllerMethod implements RedirectAbleMethod
{
    /**
     * {@inheritdoc}
     */
    protected function beforeGenerate(): self
    {
        if ($this->parentModel) {
            $this->setParameter(ucfirst($this->getParentShortName()), '$' . $this->getParentShortName());
        }
        $this->setParameter('int', '$' . $this->getModelShortName());

        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        $variable = '$' . $this->getModelShortName();
        $body = $variable . ' = ' . ucfirst($this->getModelShortName()) . '::withTrashed()->where(\'' . $this->model->getRouteKeyName() . '\',' . $variable . ')->firstOrFail()' . PHP_EOL;

        $body .= "\t\t" . $variable . '->restore();' . PHP_EOL;

        return $body;
    }
}
