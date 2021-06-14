<?php

namespace LaraCrud\Contracts\Controller;

interface ApiResourceResponseMethod
{

    public function isCollection(): bool;

    /**
     * @return string
     */
    public function resource(): string;
}
