<?php

namespace LaraCrud\Builder\Test\Methods;

class DefaultMethod extends ControllerMethod
{
    public function before()
    {


    }

    public function init(): ControllerMethod
    {
        $rules = $this->getCustomRequestClassRules();

        if (! empty($rules) && $this->reflectionMethod->getName() == '__invoke') {
            if (in_array('POST', $this->route->methods())) {
                return (new StoreMethod($this->reflectionMethod, $this->route))->setModel($this->model);
            } elseif (in_array('PUT', $this->route->methods())) {
                return (new StoreMethod($this->reflectionMethod, $this->route))->setModel($this->model);
            }
        }

        return $this;
    }
}
