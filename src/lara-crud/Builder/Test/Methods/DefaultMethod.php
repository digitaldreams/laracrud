<?php

namespace LaraCrud\Builder\Test\Methods;

use Illuminate\Support\Str;
use LaraCrud\Helpers\TemplateManager;

class DefaultMethod extends ControllerMethod
{
    public function before()
    {
        $rules = $this->getCustomRequestClassRules();
        if (! empty($rules)) {
            if (in_array('POST', $this->route->methods())) {
                $this->addToTemplate('test/api/store/a_user_can_create_a_new_model.txt');
            }
            if (in_array('PUT', $this->route->methods())) {
                $this->addToTemplate('test/api/update/a_user_can_update_his_own_model.txt');
            }
        }
        if (in_array('GET', $this->route->methods())) {
            if ($this->hasModelOnParameter) {
                if ($this->isAuthRequired()) {
                    $this->addToTemplate('test/api/show/a_user_can_see_a_model_that_he_created.txt');
                } else {
                    $this->addToTemplate('test/api/show/a_guest_can_see_a_model.txt');
                }
            } else {
                $this->addToTemplate('test/api/index/a_guest_can_see_list_of_available_models.txt');
            }
        }
    }

    public function init(): ControllerMethod
    {
        $rules = $this->getCustomRequestClassRules();

        if (! empty($rules) && $this->reflectionMethod->getName() == '__invoke') {
            if (in_array('POST', $this->route->methods())) {
                return $this->initMethod(StoreMethod::class);
            } elseif (in_array('PUT', $this->route->methods())) {
                return $this->initMethod(UpdateMethod::class);
            }
        }

        return $this;
    }

    private function initMethod($method): ControllerMethod
    {
        $update = new $method($this->reflectionMethod, $this->route);
        $update->setModel($this->model);

        if ($this->parentModel) {
            $update->setParent($this->parentModel);
        }

        return $update;
    }

    private function addToTemplate($name)
    {
        $this->testMethods[] = (new TemplateManager(
            $name,
            array_merge($this->getGlobalVariables(), [
                'data' => $this->generatePostData(),
                'modelMethodName' => Str::snake($this->reflectionMethod->getName()),
            ])
        ))->get();
    }
}
