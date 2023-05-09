<?php

namespace LaraCrud\Contracts;

interface TableContract
{
    /**
     * Find whether or not this table exists.
     *
     * @param $name
     */
    public function exists(): bool;

    public function name(): string;

    public function label(): string;

    public function icon(): string;

    public function model(): ?object;

    /**
     * @return \LaraCrud\Repositories\ColumnRepository[]
     */
    public function columns(): array;

    public function relations(): array;

    public function fileColumns(): array;

    public function hasFile(): bool;

    public function isSoftDeleteAble(): bool;

    public function searchableColumns(): array;
}
