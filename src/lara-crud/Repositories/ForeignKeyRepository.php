<?php

namespace LaraCrud\Repositories;

use LaraCrud\Contracts\ColumnContract;
use LaraCrud\Contracts\ForeignKeyContract;

class ForeignKeyRepository implements ForeignKeyContract
{
    public function name(): string
    {
        // TODO: Implement name() method.
    }

    public function referenceColumn(): string
    {
        // TODO: Implement referenceColumn() method.
    }

    public function foreignTable(): string
    {
        // TODO: Implement foreignTable() method.
    }

    public function column(): ColumnContract
    {
        // TODO: Implement column() method.
    }
}
