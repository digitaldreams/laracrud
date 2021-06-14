<?php


namespace LaraCrud\Builder\Controller\Web;

use LaraCrud\Builder\Controller\ForceDeleteMethod as ParentForceDeleteMethod;

class ForceDeleteMethod extends ParentForceDeleteMethod
{
    /**
     * @return string
     */
    public function redirectToRouteMethodName(): string
    {
        return 'index';
    }

}
