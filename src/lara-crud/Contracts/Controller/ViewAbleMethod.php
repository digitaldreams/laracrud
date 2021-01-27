<?php

namespace LaraCrud\Contracts\Controller;

interface ViewAbleMethod
{
    /**
     * @return string
     */
    public function getViewFilePath(): string;

    public function getVariables(): array;

    public function getBody(): string;
}
