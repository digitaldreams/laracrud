<?php

namespace LaraCrud\Repositories;

use Illuminate\Support\Facades\Log;
use LaraCrud\Builder\Controller\ControllerMethod;

abstract class AbstractControllerRepository
{
    /**
     * @var bool
     */
    protected bool $softDeleteAble = false;

    /**
     * @var ControllerMethod[]
     */
    protected array $methods = [];

    /**
     * All code generated my ControllerMethod classes and stored here as array of string.
     *
     * @var string[]
     */
    protected array $code = [];


    protected bool $isApi = false;

    /**
     * List of Class Full Namespace that should be imported on top of Class.
     *
     * @var array
     */
    protected array $importableNamespaces = [];

    /**
     * Loop through all of the ControllerMethod classes and getCode and importable NameSpace.
     *
     * @return $this
     */
    public function build(): self
    {
        foreach ($this->methods as $method) {
            try {
                $this->code[] = $method->getCode();
                //Only unique Full Namespace will be imported. Already added will be ignored.
                $this->importableNamespaces = array_unique(array_merge($this->importableNamespaces, array_unique($method->getNamespaces())));
            } catch (\Exception $exception) {
                Log::error($exception->getTraceAsString());
                continue;
            }
        }

        return $this;
    }

    public function getImportableNamespaces(): array
    {
        return $this->importableNamespaces;
    }

    /**
     * @return string[]
     */
    public function getCode(): array
    {
        return $this->code;
    }

    /**
     * @return $this
     */
    public function setApi(): self
    {
        $this->isApi = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function setWeb(): self
    {
        $this->isApi = false;

        return $this;
    }
}
