<?php

namespace LaraCrud;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RequestCRUD
 *
 * @author Tuhin
 */
class RequestCrud extends LaraCrud {

    protected $validateionMsg = '';

    public function __construct($table) {
        if (!empty($table)) {
            if (is_array($table)) {
                $this->tables = $table;
            } else {
                $this->tables[] = $table;
            }
        } else {
            $this->getTableList();
        }
        $this->loadDetails();
        $this->findPivotTables();
        $this->prepareRelation();
    }

    /**
     * 
     * @param type $tableName
     * @return type
     */
    private function generateContent($tableName) {
        $requestContent = $this->getTempFile('request.txt');
        $requestContent = str_replace("@@requestClassName@@", $this->getModelName($tableName) . "Request", $requestContent);
        $requestContent = str_replace("@@validationMessage@@", $this->validateionMsg, $requestContent);

        $rulesText = '';

        if (isset($this->rules[$tableName]) && !empty($this->rules[$tableName])) {

            foreach ($this->rules[$tableName] as $colName => $rules) {
                if (strlen($colName) <= 1) {
                    continue;
                }
                $rulesText.="'$colName'=>'$rules'," . "\n";
            }
        }
        $requestContent = str_replace(" @@rules@@", $rulesText, $requestContent);

        return $requestContent;
    }

    /**
     * Generate Rules for rules method of Request table
     */
    public function allRules() {

        foreach ($this->tables as $tname) {
            if (in_array($tname, $this->pivotTables)) {
                continue;
            }
            $this->rules($tname);
        }
    }

    public function rules($tname) {
        $reservedColumns = ['id', 'created_at', 'updated_at'];

        foreach ($this->tableColumns[$tname] as $column) {
            $validationRules = '';

            if (in_array($column->Field, $reservedColumns)) {
                continue;
            }

            $type = $column->Type;
            //If it contains '( )' symbol then it must hold length or string seperated by comma for enum data type
            if (strpos($type, "(")) {
                $dataType = substr($type, 0, strpos($type, "("));
                $retVals = $this->extractRulesFromType($type);

                if ($dataType == 'enum') {
                    $validationRules .= 'in:' . $retVals . '|';
                    $this->validateionMsg.="'$column->Field.in'=>''" . "\n";
                } elseif ($dataType == 'varchar') {
                    $validationRules .="max:" . $retVals . '|';
                    $this->validateionMsg.="'$column->Field.max'=>''" . "\n";
                } elseif ($dataType == 'tinyint') {
                    if ($retVals == 1) {
                        $validationRules .="boolean|";
                    }
                } elseif (in_array($dataType, ['smallint', 'int', 'mediumint', 'bigint', 'decimal', 'float', 'double'])) {
                    $validationRules .="numeric|";
                }
            } else {
                if (in_array($type, ["timestamp", 'date', 'datetime'])) {

                    $validationRules .="date|";
                    $this->validateionMsg.="'$column->Field.date'=>''" . "\n";
                } elseif ($type == 'time') {

                    $validationRules .="regex:/^([0-9]|0[0-9]|[1,2][0-3]):[0-5][0-9]?\s?(AM|PM|am|pm)?$/";
                    $this->validateionMsg.="'$column->Field.date'=>'Invalid time'" . "\n";
                } elseif ($type == 'double') {

                    $validationRules .="numeric|";
                } elseif (in_array($type, ['text', 'tinytext', 'mediumtext', 'longtext'])) {

                    $validationRules .="string|";
                }
            }
            if (isset($this->foreignKeys[$tname])) {

                if (in_array($column->Field, $this->foreignKeys[$tname]['keys']) && isset($this->foreignKeys[$tname]['rel'][$column->Field])) {

                    $tableName = $this->foreignKeys[$tname]['rel'][$column->Field]->REFERENCED_TABLE_NAME;
                    $tableColumn = $this->foreignKeys[$tname]['rel'][$column->Field]->REFERENCED_COLUMN_NAME;

                    $validationRules.='exists:' . $tableName . ',' . $tableColumn;
                    $this->validateionMsg.="'$column->Field.exists'=>''" . "\n";
                }
            } else {

                if ($column->Null == 'NO' && $column->Default == "") {
                    $validationRules.='required|';
                    $this->validateionMsg.="'$column->Field.required'=>''" . "\n";
                }
                if ($column->Key == 'UNI') {
                    $validationRules.='unique:' . $tname . ',' . $column->Field;
                    $this->validateionMsg.="'$column->Field.unique'=>''" . "\n";
                }
            }
            if (!empty($validationRules)) {
                $this->rules[$tname][$column->Field] = rtrim($validationRules, "|");
            }
        }
    }

    public function create($table) {
        try {
            $signularTable = $this->getModelName($table) . 'Request';
            $fullPath = base_path('app/Http/Requests') . '/' . $signularTable . '.php';

            if (!file_exists($fullPath)) {
                $requestContent = $this->generateContent($table);
                $this->saveFile($fullPath, $requestContent);
                return true;
            }
            return false;
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    public function make() {
        try {
            $this->allRules();
            foreach ($this->tables as $table) {
                if (in_array($table, $this->pivotTables)) {
                    continue;
                }

                $this->create($table);
            }
        } catch (\Exception $ex) {
            $this->errors[] = $ex->getMessage();
        }
    }

}
