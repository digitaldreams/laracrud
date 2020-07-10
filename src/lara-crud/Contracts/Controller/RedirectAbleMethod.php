<?php

namespace LaraCrud\Contracts\Controller;

interface RedirectAbleMethod
{
    /**
     * @return mixed
     */
    public function method(): string;

    /**
     * @return mixed
     */
    public function redirectTo(): string;

    public function flashMessage(string $message, string $key = 'message');
}
