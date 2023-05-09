<?php

namespace LaraCrud\Repositories;

use DbReader\Database;
use LaraCrud\Contracts\DatabaseContract;

class DatabaseRepository implements DatabaseContract
{
    /**
     * List of all Available Tables.
     */
    public function tables(): array
    {
        return (new Database())->tables();
    }

    /**
     * If table name mistyped and then tell user that table not found and show him a list of table.
     *
     * @param string|array $table
     *
     *
     * @throws \Exception
     */
    public function tableExists($table): bool
    {
        $insertAbleTable = !is_array($table) ? [$table] : $table;
        $availableTables = $this->tables();
        $missingTable = array_diff($insertAbleTable, $availableTables);

        if (!empty($missingTable)) {
            $message = sprintf("%s tables not found in \n %s", implode(',', $missingTable), implode("\n", $availableTables));

            throw new \Exception($message);
        }

        return true;
    }
}
