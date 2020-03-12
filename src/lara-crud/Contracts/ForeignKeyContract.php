<?php


namespace LaraCrud\Contracts;


interface ForeignKeyContract
{
    public function name(): string;

    public function referenceColumn(): string;

    public function foreignTable(): string;

    public function column(): ColumnContract;

}
