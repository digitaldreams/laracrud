<?php

namespace LaraCrud\Builder\Controller\Web;

use LaraCrud\Builder\Controller\ForceDeleteMethod as ParentForceDeleteMethod;
use LaraCrud\Contracts\Controller\RedirectAbleMethod;

class ForceDeleteMethod extends ParentForceDeleteMethod implements RedirectAbleMethod
{
    public function redirectToRouteMethodName(): string
    {
        return 'index';
    }
}
