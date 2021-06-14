<?php


namespace LaraCrud\Builder\Controller\Api;

use LaraCrud\Builder\Controller\IndexMethod as ParentIndexMethod;
use LaraCrud\Contracts\Controller\ApiResourceResponseMethod;

class IndexMethod extends ParentIndexMethod implements ApiResourceResponseMethod
{
    public function isCollection(): bool
    {
        return true;
    }

}
