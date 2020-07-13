<?php

namespace LaraCrud\Contracts\View;

interface TableContract
{
    public function columns(): array;

    public function links(): ?string;
}
