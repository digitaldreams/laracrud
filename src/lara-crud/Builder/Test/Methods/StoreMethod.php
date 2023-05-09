<?php

namespace LaraCrud\Builder\Test\Methods;

use LaraCrud\Helpers\TemplateManager;

class StoreMethod extends ControllerMethod
{
    public function before()
    {
        if ($this->isAuthRequired()) {
            $this->testMethods[] = (new TemplateManager(
                'test/api/store/a_guest_cannot_create_a_new_model.txt',
                array_merge($this->getGlobalVariables(), ['data' => $this->generatePostData()])
            ));
        }

        $this->testMethods[] = (new TemplateManager(
            'test/api/store/a_user_can_create_a_new_model.txt',
            array_merge($this->getGlobalVariables(), ['data' => $this->generatePostData()])
        ));

        $this->testMethods[] = (new TemplateManager(
            'test/api/store/create_new_model_validation_check.txt',
            $this->getGlobalVariables()
        ));

        $this->testMethods[] = (new TemplateManager(
            'test/api/store/createNewModelValidationProvider.txt',
            array_merge($this->getGlobalVariables(), ['data' => $this->generateDataProvider()])
        ));
    }
}
