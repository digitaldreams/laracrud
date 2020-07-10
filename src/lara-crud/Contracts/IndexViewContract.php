<?php

namespace LaraCrud\Contracts;

interface IndexViewContract extends PageViewContract
{
    /**
     * @return string
     */
    public function label(): string;

    /**
     * @return string|null
     */
    public function searchForm(): ?string;

    /**
     * @return string|null
     */
    public function recycleBin(): ?string;

    /**
     * Either form or modal.
     *
     * @return string
     */
    public function editType(): string;

    /**
     * Either Table or Card.
     *
     * @return string
     */
    public function displayType(): string;
}
