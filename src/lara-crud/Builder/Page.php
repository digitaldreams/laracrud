<?php

namespace LaraCrud\Builder;

abstract class Page
{

    public $name;

    protected $page;

    protected $type;

    protected $inputType;

    protected $filePath;

    public function __construct(protected $data)
    {
    }

    /**
     * @return mixed
     */
    abstract public function render();

    /**
     * @return mixed
     */
    abstract public function save();
}
