<?php
/**
 * Created by PhpStorm.
 * User: Tuhin
 * Date: 4/27/2017
 * Time: 3:15 PM
 */

namespace LaraCrud\Builder;


abstract class  Page
{
    public $name;
    protected $data;
    protected $page;
    protected $type;
    protected $inputType;
    protected $filePath;

    public function __construct($data)
    {
        $this->data=$data;
    }

    public abstract function render();

    public abstract function save();

}