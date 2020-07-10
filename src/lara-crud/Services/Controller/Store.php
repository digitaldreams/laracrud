<?php

namespace LaraCrud\Services\Controller;

use LaraCrud\Contracts\Controller\RedirectAbleMethod;

class Store extends ControllerMethod implements RedirectAbleMethod
{

    public function method(): string
    {
        // TODO: Implement method() method.
    }

    public function redirectTo(): string
    {
        // TODO: Implement redirectTo() method.
    }

    public function flashMessage(string $message, string $key = 'message')
    {
        // TODO: Implement flashMessage() method.
    }
}
