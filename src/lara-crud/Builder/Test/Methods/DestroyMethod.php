<?php

namespace LaraCrud\Builder\Test\Methods;

use LaraCrud\Helpers\TemplateManager;

class DestroyMethod extends ControllerMethod
{
    public function before(): void
    {
        if ($this->isAuthRequired()) {
            if ($this->isAuthRequired()) {
                $this->testMethods[] = (new TemplateManager('test/api/destroy/a_guest_cannot_delete_the_model_of_a_user.txt', $this->getGlobalVariables()));
                if ($this->hasSuperAdminRole()) {
                    $this->testMethods[] = (new TemplateManager('test/api/destroy/a_super_admin_can_delete_others_model.txt', $this->getGlobalVariables('$superAdmin')));
                }
                $this->testMethods[] = (new TemplateManager('test/api/destroy/a_user_can_delete_his_own_model.txt', $this->getGlobalVariables()));
                $this->testMethods[] = (new TemplateManager('test/api/destroy/a_user_cannot_delete_others_model.txt', $this->getGlobalVariables('$secondUser')));
            } else {
                throw new \Exception('Dangerous Delete method must required Authentication');
            }
        }
    }
}
