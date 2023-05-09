<?php

namespace LaraCrud\Repositories;

use Illuminate\Database\Eloquent\Model;
use LaraCrud\Builder\Controller\ControllerMethod;
use LaraCrud\Configuration;

class ControllerRepository extends AbstractControllerRepository
{
    /**
     * @param \LaraCrud\Builder\Controller\ControllerMethod $method
     *
     * @return \LaraCrud\Repositories\ControllerRepository
     */
    public function addMethod(ControllerMethod $method): self
    {
        $this->methods[] = $method;

        return $this;
    }

    /**
     * @param string[]                                 $methods
     * @param \Illuminate\Database\Eloquent\Model      $model
     * @param \Illuminate\Database\Eloquent\Model|null $parent
     *
     * @return $this
     */
    public function addMethodsFromString(array $methods, Model $model, ?Model $parent = null): self
    {
        $availableMethods = $this->isApi ? Configuration::$controllerApiMethods : Configuration::$controllerWebMethods;

        $insertAbleMethods = array_intersect_key($availableMethods, array_flip($methods));
        foreach ($insertAbleMethods as $methodName) {
            $method = new $methodName($model);
            if (!empty($parent)) {
                $method->setParent($parent);
            }
            $this->addMethod($method);
        }
        return $this;
    }
}
