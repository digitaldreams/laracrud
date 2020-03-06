<?php

namespace LaraCrud\Builder;

abstract class Page
{
    public $name;
    protected $data;
    protected $page;
    protected $type;
    protected $inputType;
    protected $filePath;

    public function __construct($data)
    {
        $this->data = $data;
    }

    abstract public function render();

    abstract public function save();
}
