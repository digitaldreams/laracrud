<?php

namespace LaraCrud\Builder\Test\Methods;

use LaraCrud\Helpers\TemplateManager;

class UpdateMethod extends ControllerMethod
{
    public function before()
    {
        if ($this->isAuthRequired()) {
            $this->testMethods[] = (new TemplateManager(
                'test/api/update/a_user_can_update_his_own_model.txt',
                array_merge($this->getGlobalVariables(), ['data' => $this->generatePostData(true)])
            ));

            $this->testMethods[] = (new TemplateManager(
                'test/api/update/a_guest_cannot_update_a_model.txt',
                array_merge($this->getGlobalVariables(), ['data' => $this->generatePostData(true)])
            ));

            if ($this->modelRelationReader->hasOwner()) {
                $this->testMethods[] = (new TemplateManager(
                    'test/api/update/a_user_cannot_update_others_model.txt',
                    array_merge($this->getGlobalVariables('$secondUser'), ['data' => $this->generatePostData(true)])
                ));
            }

            if ($this->hasModelOnParameter && $this->hasSuperAdminRole()) {
                $this->testMethods[] = (new TemplateManager(
                    'test/api/update/a_super_admin_can_update_any_model.txt',
                    $this->getGlobalVariables('$superAdmin')
                ));
            }

            $this->testMethods[] = (new TemplateManager(
                'test/api/update/update_existing_model_validation_check.txt',
                $this->getGlobalVariables()
            ));

            $this->testMethods[] = (new TemplateManager(
                'test/api/update/updateModelValidationProvider.txt',
                array_merge($this->getGlobalVariables(), ['data' => $this->generateDataProvider()])
            ));
        }
    }
}
