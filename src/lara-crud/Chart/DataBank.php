<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LaraCrud\Chart;

use LaraCrud\LaraCrud;
use JsonSerializable;

/**
 * Description of Generator
 *
 * @author Tuhin
 */
class DataBank extends LaraCrud implements JsonSerializable
{
    const CREATED_AT = 'created_at';

    /**
     * Start date of record
     * @var \DateTime
     */
    protected $start_date = '';

    /**
     * End Date of record
     * @var \DateTime
     */
    protected $end_date = '';

    /**
     * Data fetch from db will be stored here.
     * @var array
     */
    protected $data = [];

    /**
     * Total Number of record each table
     *
     * @var array
     *
     * [
     * tableName=>totalRecord
     * ]
     *
     */
    protected $total = [];

    /**
     * Database Connection
     * @var \Illuminate\Database\Connection
     */
    protected $db;

    /**
     *
     * @param array $tables List of tables that will be used for data generation
     * @param \DateTime $start_date any valid date String e.g. 12 november 2015 or 11/12/2016 or Object of DateTime
     * @param \DateTime $end_date any valid date String e.g. 12 november 2015 or 11/12/2016 or Object of DateTime
     */
    public function __construct(array $tables = [], $start_date = '', $end_date = '')
    {
        parent::__construct();

        if (!empty($tables)) {
            $this->tables = $tables;
        }

        if (!empty($start_date)) {
            $this->start_date = ($start_date instanceof \DateTime) ? $start_date : new \DateTime($start_date);
        }

        if (!empty($end_date)) {
            $this->end_date = ($end_date instanceof \DateTime) ? $end_date : new \DateTime($end_date);
        }
        $this->db = app('db');
        $this->getTableList();
        $this->loadDetails();
        $this->prepareRelation();
    }

    /**
     * Set Database Connection
     * @param \Illuminate\Database\Connection $pdo
     */
    public function setDB($db)
    {
        $this->db = $db;
    }

    /**
     * Fetch total number of recrods of all tables and stored to total property
     */
    public function total()
    {
        $this->db->beginTransaction();
        foreach ($this->tables as $table) {
            $this->total[$table] = $this->getTotal($table);
        }
        $this->db->commit();
    }

    protected function getTotal($table)
    {
        return $this->addWhere($this->db->table($table))->count();
    }

    /**
     * 
     * @param \Illuminate\Database\Connection $db
     *
     * @return \Illuminate\Database\Connection Description
     */
    protected function addWhere($db)
    {
        if (!empty($this->start_date)) {
            $db = $db->where(static::CREATED_AT, '>=', $this->start_date->format('Y-m-d H:i:s'));
        }
        if (!empty($this->end_date)) {
            $db = $db->where(static::CREATED_AT, '<=', $this->end_date->format('Y-m-d H:i:s'));
        }
        return $db;
    }

    public function column($table, $column, $groupBy = '')
    {
        $column = isset($this->tableColumns[$table][$column]) ? $this->tableColumns[$table][$column] : FALSE;
        if ($column) {
            
        }
    }

    public function __get($name)
    {
        if (isset($this->total[$name])) {
            return $this->total[$name];
        }
        return false;
    }

    public function jsonSerialize()
    {
        return [
            'tables' => $this->total
        ];
    }

    public function __sleep()
    {
        ;
    }

    public function __wakeup()
    {
        ;
    }
}