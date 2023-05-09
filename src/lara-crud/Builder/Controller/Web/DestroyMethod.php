<?php

namespace LaraCrud\Builder\Controller\Web;

use LaraCrud\Builder\Controller\DestroyMethod as ParentDestroyMethod;
use LaraCrud\Contracts\Controller\RedirectAbleMethod;

class DestroyMethod extends ParentDestroyMethod implements RedirectAbleMethod
{
    /**
     * Name of the Route user will be redirected after successfully Delete.
     */
    public function redirectToRouteMethodName(): string
    {
        return 'index';
    }
}
