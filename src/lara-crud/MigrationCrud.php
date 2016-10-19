<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace LaraCrud;

/**
 * Description of MigrationCrud
 *
 * @author Tuhin
 */
class MigrationCrud extends LaraCrud
{
    protected $fk        = [];
    protected $columnMap = [
        'varchar' => 'string',
        'char' => 'char',
        'text' => 'text',
        'mediumtext' => 'mediumText',
        'longtext' => 'longText',
        'int' => 'integer',
        'tinyint' => 'tinyInteger',
        'smallint' => 'smallInteger',
        'mediumint' => 'mediumInteger',
        'bigint' => 'bigInteger',
        'float' => 'float',
        'double' => 'double',
        'decimal' => 'decimal',
        'int' => 'boolean',
        'enum' => 'enum',
        'date' => 'date',
        'datetime' => 'dateTime',
        'time' => 'time',
        'timestamp' => 'timestamp',
    ];

    public function __construct($table)
    {
        parent::__construct();
        if (!empty($table)) {
            $insertAbleTable = $table;

            if (!is_array($table)) {
                $insertAbleTable = [$table];
            }


            $this->tables = $insertAbleTable;
        } else {
            $this->getTableList();
        }
        $this->loadDetails();
        $this->columnDataTypes();
        $this->prepareRelation();
    }

    public function makeRules()
    {
        foreach ($this->tables as $table) {
            
        }
    }

    private function rules($table)
    {
        $retArr = [];
        if (!isset($this->tableColumns[$table])) {
            return false;
        }
        foreach ($this->tableColumns[$table] as $column) {
            $arr          = [];
            $params       = '';
            $otherMethods = [];

            $arr['columnName'] = $columnName        = $column->Field;
            $dataType          = isset($this->columnsDataType[$table][$columnName]) ? $this->columnsDataType[$table][$columnName] : '';

            if ($column->Key == 'PRI') {
                if ($dataType == 'int') {
                    $arr['methodName'] = 'increments';
                } elseif ($dataType == 'bigint') {
                    $arr['methodName'] = 'bigIncrements';
                }
            } else {
                $arr['methodName'] = isset($this->columnMap[$table][$dataType]) ? $this->columnMap[$table][$dataType] : '';
            }

            if (strpos($column->Type, "(")) {
                $retVals = $this->extractRulesFromType($column->Type);
                //for enum data type we will use in validator.
                if ($dataType == 'enum') {
                    $params = '['.$retVals.']';
                    $this->validateionMsg.="\t"."\t"."'$column->Field.in'=>''"."\n";
                } elseif ($dataType == 'varchar') {
                    $params = $retVals;
                    $this->validateionMsg.="\t"."\t"."'$column->Field.max'=>''"."\n";
                } elseif ($dataType == 'tinyint') {
                    if ($retVals == 1) {
                        $arr['methodName'] = 'boolean';
                    }
                } elseif (in_array($dataType, ['smallint', 'int', 'mediumint', 'bigint', 'float',
                        'double'])) {
                    if (!empty($retVals)) {
                        $params = $retVals;
                    }
                } elseif ($dataType == 'decimal') {
                    $startBrace = stripos($column->Type, "(");
                    $endBrace   = stripos($column->Type, ")");
                    $pm         = substr($column->Type, $startBrace, ($endBrace - $startBrace));
                    if (!empty($pm)) {
                        $params = $pm;
                    }
                }
            }
            if ($column->Null == 'YES') {
                $otherMethods[] = [
                    'name' => 'nullable',
                    'params' => ''
                ];
            }
            if (!empty($column->Default)) {
                $otherMethods[] = [
                    'name' => 'default',
                    'params' => $column->Default
                ];
            }
            if ($column->Key == 'uni') {
                $otherMethods[] = [
                    'name' => 'unique',
                    'params' => $column->Default
                ];
            }
            if (isset($this->foreignKeys[$table]['keys'])) {
                if (in_array($columnName, $this->foreignKeys[$table]['keys'])) {
                    $otherMethods[]   = [
                        'name' => 'unsigned',
                        'params' => ''
                    ];
                    $this->fk[$table] = $this->foreignKeys[$table]['rel'];
                }
            }


            $arr['mainParams'] = $params;


            $retArr[$columnName] = $arr;
        }
    }

    protected function readDir()
    {
        
    }

    protected function arrangeTables()
    {
        
    }

    public function make()
    {
        return $this->tableColumns;
    }
}