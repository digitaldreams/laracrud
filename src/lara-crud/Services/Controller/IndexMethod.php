<?php

namespace LaraCrud\Services\Controller;

use Illuminate\Support\Str;
use LaraCrud\Contracts\Controller\ViewAbleMethod;
use LaraCrud\Services\FullTextSearch;

class IndexMethod extends ControllerMethod implements ViewAbleMethod
{
    /**
     * @return bool
     */
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

    /**
     * @return string
     */
    public function getBody(): string
    {
        $body = '$builder = ' . $this->getModelShortName() . '::';
        $body .= $this->isSearchAble() ? 'search($request->get(\'search\'))' : 'query()';

        return $body . ';';
    }
}
