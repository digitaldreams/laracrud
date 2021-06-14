<?php

namespace LaraCrud\Builder\Controller\Web;

use Illuminate\Support\Str;
use LaraCrud\Builder\Controller\ControllerMethod;
use LaraCrud\Contracts\Controller\RedirectAbleMethod;
use LaraCrud\Builder\Controller\StoreMethod as ParentStoreMethod;

class StoreMethod extends ParentStoreMethod implements RedirectAbleMethod
{

}
