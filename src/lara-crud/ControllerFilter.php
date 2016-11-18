<?php

namespace LaraCrud;

/**
 * Description of ArrayFilter
 *
 * @author Tuhin
 */
class ControllerFilter extends \FilterIterator
{
    public $controllerName;

    public function accept()
    {
        $current = parent::current();
        if (is_object($current) && isset($current->class) && $current->class == $this->controllerName) {
            return $current;
        }
    }

    public function setClassName($className)
    {
        $this->controllerName = $className;
    }
}