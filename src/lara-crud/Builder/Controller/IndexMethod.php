<?php

namespace LaraCrud\Builder\Controller;

use Illuminate\Support\Str;
use LaraCrud\Builder\Controller\ControllerMethod;
use LaraCrud\Services\FullTextSearch;

abstract class IndexMethod extends ControllerMethod
{
    public function isSearchAble(): bool
    {
        $traits = class_uses($this->model);

        return in_array('Laravel\Scout\Searchable', $traits) || in_array(FullTextSearch::class, $traits);
    }

    /**
     * Set necessary data.
     *
     * @return $this
     * @throws \ReflectionException
     *
     */
    protected function beforeGenerate(): self
    {
        $this->setParameter('Request', '$request');

        $this->setParentVariableAndParam()
            ->setVariable(Str::plural($this->getModelVariableName()), '$builder->paginate(10)');

        return $this;
    }

    public function getBody(): string
    {
        $body = '$builder = ' . $this->getModelShortName() . '::';
        $body .= $this->isSearchAble() ? 'search($request->get(\'search\'))' : 'query()';

        return $body . ';';
    }

    public function phpDocComment(): string
    {
        return sprintf('Display a listing of %s', $this->getModelShortName());
    }
}
