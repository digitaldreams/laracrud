<?php

namespace LaraCrud\Contracts;

interface HtmlTableContract
{
    public function model(): ModelContract;

    public function headers(): array;

    public function columns(): array;

    public function orders(): array;

    public function editLinkContent(): ?string;

    public function deleteLinkContent(): ?string;
}
