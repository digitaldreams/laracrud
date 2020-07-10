<?php

namespace LaraCrud\Services\Controller;

use Illuminate\Support\Str;
use LaraCrud\Contracts\Controller\RedirectAbleMethod;

class Store extends ControllerMethod implements RedirectAbleMethod
{
    /**
     * @return \LaraCrud\Services\Controller\ControllerMethod|void
     * @throws \ReflectionException
     */
    protected function beforeGenerate()
    {
        $requestClass = $this->getRequestClass();
        $this->setParameter($requestClass, '$request');

        if ($this->parentModel) {
            $this->setParameter($this->getParentShortName(), '$' . lcfirst($this->getParentShortName()));
        }
        return $this;
    }

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getBody(): string
    {
        $variable = '$' . lcfirst($this->getModelShortName());
        $body = $variable . ' = new ' . $this->getModelShortName() . ';' . PHP_EOL;
        if ($this->parentModel) {
            $body .= "\t\t" . $variable . '->' . Str::snake($this->getParentShortName()) . '_id = $' . lcfirst($this->getParentShortName()) . '->id' . PHP_EOL;
        }
        $body .= "\t\t" . $variable . '->fill($request->all())->save();' . PHP_EOL;
        return $body;
    }
}
