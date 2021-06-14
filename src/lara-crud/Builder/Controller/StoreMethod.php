<?php

namespace LaraCrud\Builder\Controller;

use Illuminate\Support\Str;
use LaraCrud\Builder\Controller\ControllerMethod;

abstract class StoreMethod extends ControllerMethod
{
    /**
     * {@inheritdoc}
     */
    protected function beforeGenerate(): self
    {
        $requestClass = $this->getRequestClass();
        $this->setParameter($requestClass, '$request');

        return $this->setParentVariableAndParam();
    }

    /**
     * @return string
     *
     * @throws \ReflectionException
     */
    public function getBody(): string
    {
        $variable = '$' . $this->getModelVariableName();
        $body = $variable . ' = new ' . $this->getModelShortName() . ';' . PHP_EOL;
        //Assign something like $comment->post_id = $post->id;
        if ($this->parentModel) {
            $body .= "\t\t" . $variable . '->' . Str::snake($this->getParentVariableName()) . '_id = $' . $this->getParentVariableName() . '->id;' . PHP_EOL;
        }

        $body .= "\t\t" . $variable . '->fill($request->all())->save();' . PHP_EOL;

        return $body;
    }


    public function phpDocComment(): string
    {
        return sprintf('Store a newly created %s in storage.', $this->getModelShortName());
    }
}
