<?php

namespace LaraCrud\Helpers;

trait DatabaseHelper
{
    /**
     * Get connected database name.
     *
     * @return bool
     */
    public function getDatabaseName()
    {
        $result = $this->db->query('SELECT DATABASE() as db')->fetch();

        return isset($result->db) ? $result->db : false;
    }

    /**
     * Get the PDO object.
     *
     * @return mixed
     */
    public function db()
    {
        return $this->db;
    }

    /**
     * Show all table in current database.
     *
     * @return array
     */
    public function tables()
    {
        $tableNames = [];
        $result = $this->db->query('SHOW TABLES')->fetchAll(\PDO::FETCH_OBJ);
        foreach ($result as $tb) {
            $tb = (array) $tb;
            $tableName = array_values($tb);
            $tableNames[] = array_shift($tableName);
        }

        return $tableNames;
    }
}
