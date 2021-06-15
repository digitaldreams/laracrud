<?php


namespace LaraCrud\Repositories;

use Illuminate\Database\Eloquent\Model;
use LaraCrud\Helpers\Helper;

class TestRepository extends AbstractControllerRepository
{
    use Helper;

    public function __construct(string $controller, bool $isApi = false, ?Model $model = null, ?Model $parent = null)
    {
        if (!class_exists($controller)) {
            $ns = $isApi == true ? config('laracrud.controller.apiNamespace') : config('laracrud.controller.namespace');
            $fullNs = $this->getFullNS($ns);
            $controller = $fullNs . '\\' . $controller;
        }

        if (!class_exists($controller)) {
            throw new \Exception(sprintf('Unable to find %s', $controller));
        }

    }

}
