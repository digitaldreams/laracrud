<?php

namespace LaraCrud\Services\Controller;

use Illuminate\Support\Str;
use LaraCrud\Contracts\ViewAbleMethod;
use LaraCrud\Services\FullTextSearch;
use Laravel\Scout\Searchable;

class Index extends ControllerMethod implements ViewAbleMethod
{
    /**
     * @return bool
     */
    public function isSearchAble()
    {
        $traits = class_uses($this->model);

        return in_array(Searchable::class, $traits) || in_array(FullTextSearch::class, $traits);
    }

    /**
     * Set necessary data.
     *
     * @return $this
     *
     * @throws \ReflectionException
     */
    protected function beforeGenerate()
    {
        $this->setParameter('Request', '$request');

        if ($this->parentModel) {
            $this->setVariable(lcfirst($this->getParentShortName()), '$' . lcfirst($this->getParentShortName()))
                ->setParameter($this->getParentShortName(), '$' . lcfirst($this->getParentShortName()));
        }

        $this->setVariable(Str::plural(lcfirst($this->getModelShortName())), '$builder->paginate(10)');

        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        $body = '$builder = ' . $this->modelShortName . '::';
        $body .= $this->isSearchAble() ? 'search($request->get(\'search\'))' : 'query';

        return $body;
    }
}
