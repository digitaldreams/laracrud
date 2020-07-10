<?php

namespace LaraCrud\Repositories;

use DbReader\Database;
use LaraCrud\Contracts\DatabaseContract;

class DatabaseRepository implements DatabaseContract
{
    public function tables()
    {
        return (new Database())->tables();
    }
    /**
     * If table name mistyped and then tell user that table not found and show him a list of table.
     *
     * @param string $table
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function tableExists($table)
    {
        $insertAbleTable = !is_array($table) ? [$table] : $table;
        $availableTables = $this->tables();
        $missingTable = array_diff($insertAbleTable, $availableTables);

        if (!empty($missingTable)) {
            $message = implode(',', $missingTable) . ' tables not found in ' . "\n" . implode("\n", $availableTables);
            throw new \Exception($message);
        }

        return true;
    }
}
