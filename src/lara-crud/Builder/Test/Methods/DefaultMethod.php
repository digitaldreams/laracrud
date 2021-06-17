<?php

namespace LaraCrud\Builder\Test\Methods;

class DefaultMethod extends ControllerMethod
{
    public function before()
    {
        exit('I am calling');
    }
}
