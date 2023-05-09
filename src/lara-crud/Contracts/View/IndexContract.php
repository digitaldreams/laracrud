<?php

namespace LaraCrud\Contracts\View;

interface IndexContract
{
    public function searchForm(): ?string;

    public function recycleBin(): ?string;

    /**
     * Either Table or Card.
     */
    public function displayType(): object;
}
