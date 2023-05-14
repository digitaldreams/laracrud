<?php

namespace LaraCrud\Contracts;

interface ClassGeneratorContract extends FileGeneratorContract
{

    public function getClassName(): string;

    public function getNamespace(): string;
}
