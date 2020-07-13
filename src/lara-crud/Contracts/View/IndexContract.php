<?php

namespace LaraCrud\Contracts\View;

interface IndexContract
{
    /**
     * @return string|null
     */
    public function searchForm(): ?string;

    /**
     * @return string|null
     */
    public function recycleBin(): ?string;

    /**
     * Either Table or Card.
     *
     * @return object
     */
    public function displayType(): object;
}
