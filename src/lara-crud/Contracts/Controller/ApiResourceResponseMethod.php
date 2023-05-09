<?php

namespace LaraCrud\Contracts\Controller;

interface ApiResourceResponseMethod
{
    public function isCollection(): bool;

    public function resource(): string;
}
