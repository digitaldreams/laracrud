<?php

namespace LaraCrud\Repositories;

use Illuminate\Database\Eloquent\Model;
use LaraCrud\Builder\Controller\ControllerMethod;
use LaraCrud\Configuration;

class ControllerRepository extends AbstractControllerRepository
{
    public function addMethod(ControllerMethod $controllerMethod): self
    {
        $this->methods[] = $controllerMethod;

        return $this;
    }

    /**
     * @param string[]                                 $methods
     *
     * @return $this
     */
    public function addMethodsFromString(array $methods, Model $model, ?Model $parent = null): self
    {
        $availableMethods = $this->isApi ? Configuration::$controllerApiMethods : Configuration::$controllerWebMethods;

        $insertAbleMethods = array_intersect_key($availableMethods, array_flip($methods));
        foreach ($insertAbleMethods as $insertAbleMethod) {
            $method = new $insertAbleMethod($model);
            if (!empty($parent)) {
                $method->setParent($parent);
            }
            $this->addMethod($method);
        }
        return $this;
    }
}
