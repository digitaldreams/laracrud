<?php


namespace LaraCrud\Builder\Test\Methods;


use Illuminate\Foundation\Http\FormRequest;
use LaraCrud\Helpers\TemplateManager;

class StoreMethod extends ControllerMethod
{

    public function before()
    {
        if ($this->isAuthRequired()) {
            $this->testMethods[] = (new TemplateManager(
                'test/api/store/a_user_can_create_a_new_model.txt',
                array_merge($this->getGlobalVariables(), ['data' => $this->generatePostData()])
            ));
        }
    }

    public function getCustomRequestClass()
    {
        $rules = [];
        try {
            foreach ($this->reflectionMethod->getParameters() as $parameter) {
                if ($parameter->hasType()) {
                    if (is_subclass_of($parameter->getType()->getName(), FormRequest::class)) {
                        $className = $parameter->getType()->getName();
                        $rfm = new \ReflectionMethod($parameter->getType()->getName(), 'rules');
                        $rules = $rfm->invoke(new $className);
                    }
                }
            }
        } catch (\Exception $e) {

        }

        return $rules;
    }

    public function generatePostData()
    {
        $data = '';
        $this->getCustomRequestClass();
        return $data;
    }
}
