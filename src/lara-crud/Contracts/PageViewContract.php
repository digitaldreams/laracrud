<?php


namespace LaraCrud\Contracts;

interface PageViewContract
{
    public function name(): string;

    public function path(): string;

    public function title(): string;

    public function table(): TableContract;

}
