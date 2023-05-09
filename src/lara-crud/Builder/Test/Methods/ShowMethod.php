<?php

namespace LaraCrud\Builder\Test\Methods;

use LaraCrud\Helpers\TemplateManager;

class ShowMethod extends ControllerMethod
{
    public function before()
    {
        if ($this->isAuthRequired()) {
            $this->testMethods[] = (new TemplateManager('test/api/show/a_user_can_see_a_model_that_he_created.txt', $this->getGlobalVariables()));
            $this->testMethods[] = (new TemplateManager('test/api/show/a_guest_cannot_see_a_model.txt', $this->getGlobalVariables()));
            $this->testMethods[] = (new TemplateManager('test/api/show/a_user_cannot_see_others_model.txt', $this->getGlobalVariables('$secondUser')));
        } else {
            $this->testMethods[] = (new TemplateManager('test/api/show/a_guest_can_see_a_model.txt', $this->getGlobalVariables()));
        }
    }
}
