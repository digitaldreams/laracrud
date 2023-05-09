<?php

namespace LaraCrud\Builder\Test\Methods;

use LaraCrud\Helpers\TemplateManager;

class IndexMethod extends ControllerMethod
{
    public function before(): void
    {
        if ($this->isAuthRequired()) {
            $this->testMethods[] = (new TemplateManager('test/api/index/a_user_can_see_list_of_models_that_he_created.txt', $this->getGlobalVariables()));
            $this->testMethods[] = (new TemplateManager('test/api/index/a_guest_cannot_see_list_of_models.txt', $this->getGlobalVariables()));
        } else {
            $this->testMethods[] = (new TemplateManager('test/api/index/a_guest_can_see_list_of_available_models.txt', $this->getGlobalVariables()));
        }
    }
}
