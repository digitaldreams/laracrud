<?php

namespace LaraCrud\Contracts\Controller;

interface RedirectAbleMethod
{
    /**
     * @return mixed
     */
    public function route(): string;

    public function getFlashMessage();

    public function getFlashMessageKey();
}
