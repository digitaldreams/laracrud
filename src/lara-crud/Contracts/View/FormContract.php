<?php

namespace LaraCrud\Contracts\View;

interface FormContract
{
    public function name(): string;

    public function columns(): array;

    public function method(): string;

    public function action(): string;

    public function saveBtnLabel(): string;

    /**
     * Multiple Line, Inline, Double.
     */
    public function layoutType(): string;

    public function orders(): array;
}
