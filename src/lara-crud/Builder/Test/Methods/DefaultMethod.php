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
                $store = new StoreMethod($this->reflectionMethod, $this->route);
                $store->setModel($this->model);
                if ($this->parentModel) {
                    $store->setParent($this->parentModel);
                }

                return $store;
            } elseif (in_array('PUT', $this->route->methods())) {
                $update = new UpdateMethod($this->reflectionMethod, $this->route);
                $update->setModel($this->model);

                if ($this->parentModel) {
                    $update->setParent($this->parentModel);
                }

                return $update;
            }
        }

        return $this;
    }
}
