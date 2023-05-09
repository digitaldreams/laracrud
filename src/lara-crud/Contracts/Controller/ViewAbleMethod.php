<?php

namespace LaraCrud\Contracts\Controller;

interface ViewAbleMethod
{
    public function getViewFilePath(): string;

    public function getVariables(): array;

    public function getBody(): string;
}
