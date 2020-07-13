<?php

namespace LaraCrud\Contracts\View;

interface FormContract
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return array
     */
    public function columns(): array;

    /**
     * @return string
     */
    public function method(): string;

    /**
     * @return string
     */
    public function action(): string;

    /**
     * @return string
     */
    public function saveBtnLabel(): string;

    /**
     * Multiple Line, Inline, Double.
     *
     * @return string
     */
    public function layoutType(): string;

    /**
     * @return array
     */
    public function orders(): array;
}
