<?php
/**
 * Created by PhpStorm.
 * User: Tuhin
 * Date: 10/4/2018
 * Time: 9:50 AM
 */

namespace LaraCrud\Helpers;


use DbReader\Column;

class FakerColumn
{
    protected $map = [];
    /**
     * @var Column
     */
    protected $column;

    public function __construct(Column $column)
    {
        $this->column = $column;
    }

    public function get()
    {

    }

    protected function default()
    {
        switch ($this->column->type()) {
            case 'varchar':
                break;
            case 'enum':
                break;
            case 'longText':
            case 'mediumtext':
            case 'text':
            case 'tinytext':
                break;
            // Numeric data Type
            case 'bigint':
                break;
            case 'mediumint':
                break;
            case 'int':
                break;
            case 'smallint':
                break;
            case 'tinyint':
                break;
            case 'decimal':
                break;
            case 'float':
                break;
            case 'double':
                break;
            // Date Time
            case 'date':
                break;
            case 'datetime':
                break;
            case 'time':
            case 'year':
                break;
                break;
            case 'timestamp':
                break;

        }
    }
}