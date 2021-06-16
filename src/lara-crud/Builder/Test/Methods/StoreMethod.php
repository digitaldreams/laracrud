<?php


namespace LaraCrud\Builder\Test\Methods;


use LaraCrud\Helpers\TemplateManager;

class StoreMethod extends ControllerMethod
{

    public static array $ignoreDataProviderRules = [
        'nullable',
        'string',
        'numeric',
    ];


    public function before()
    {
        if ($this->isAuthRequired()) {
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

    public function generatePostData()
    {
        $data = '';
        $rules = $this->getCustomRequestClassRules();
        foreach ($rules as $field => $rule) {
            $data .= "\t\t\t" . '"' . $field . '" => ' . $this->getModelVariable() . '->' . $field . ',' . PHP_EOL;
        }

        return $data;
    }

}
