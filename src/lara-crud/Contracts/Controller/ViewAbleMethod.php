<?php

namespace LaraCrud\Contracts;

interface ViewAbleMethod
{
    /**
     * @return string
     */
    public function getViewFilePath(): string;

    public function getVariables(): array;

    public function getBody(): string;
}
