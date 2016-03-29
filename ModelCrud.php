<?php

namespace App\Libs;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ModelCrud
 *
 * @author Tuhin
 */
class ModelCrud extends LaraCrud {

    public $propertyDefiner = '';

    public function __construct($table = '') {
        if (!empty($table)) {
            $this->tables[] = $table;
        } else {
            $this->getTableList();
        }
        $this->loadDetails();
        $this->prepareRelation();
    }

    /**
     * Generate scopeMethods for a Model
     */
    protected function prepareScopes($tableName) {

        $scopePlaceholders = '';
        $propertyDefiner = '';
        if (isset($this->tableColumns[$tableName])) {
            foreach ($this->tableColumns[$tableName] as $column) {
                $type = $column->Type;
                if (empty($column->Field)) {
                    continue;
                }
                if (strpos($type, "(")) {
                    $type = substr($column->Type, 0, strpos($column->Type, "("));
                }
                $propertyDefiner.='@property ' . $type . ' $' . $column->Field . ' ' . str_replace("_", " ", $column->Field) . "\n";
                $scopeTemplateStr = file_get_contents(__DIR__ . '/templates/scope.txt');
                $scopeMethodName = ucfirst(camel_case($column->Field));
                $scopeTemplateStr = str_replace("@@methodName@@", $scopeMethodName, $scopeTemplateStr);
                $scopeTemplateStr = str_replace("@@fielName@@", $column->Field, $scopeTemplateStr);
                $scopePlaceholders.=$scopeTemplateStr . "\n\n";
            }
        }
        $this->propertyDefiner = $propertyDefiner;
        return $scopePlaceholders;
    }

    protected function prepareRelationShip($tableName) {
        $relationShipsStr = '';

        if (isset($this->finalRelationShips[$tableName]) && !empty($this->finalRelationShips[$tableName])) {
            $params = '';

            foreach ($this->finalRelationShips[$tableName] as $rls) {
                if (empty($rls['model'])) {
                    continue;
                }
                $newCloneRelation = file_get_contents(__DIR__ . '/templates/relationship.txt');
                $paramsNt = '';
                $newCloneRelation = str_replace("@@methodName@@", lcfirst($rls['model']), $newCloneRelation);
                $newCloneRelation = str_replace("@@modelName@@", $rls['model'], $newCloneRelation);
                $newCloneRelation = str_replace("@@relationShip@@", $rls['name'], $newCloneRelation);
                $newCloneRelation = str_replace("@@namespace@@", $this->namespace, $newCloneRelation);

                if (isset($rls['pivotTable']) && $rls['name'] = 'belongsToMany') {
                    $paramsNt = "," . "'" . $rls['pivotTable'] . "'";
                } else {
                    $paramsNt = "," . "'" . $rls['foreign_key'] . "'";
                }

                $newCloneRelation = str_replace("@@params@@", $paramsNt, $newCloneRelation);

                $relationShipsStr.=$newCloneRelation . "\n\n";
            }
        }
        return $relationShipsStr;
    }

    protected function prepareConstant($tableName) {
        $constantsStr = '';

        foreach ($this->tableColumns[$tableName] as $column) {
            $type = $column->Type;

            $dataType = substr($type, 0, strpos($type, "("));

            $retVals = $this->extractRulesFromType($type);

            if (in_array($column->Type, ['time', 'date', 'datetime', 'timestamp'])) {
                $this->dateColumns[$tableName][] = $column->Field;
            }

            if (strpos($type, "(") && $dataType == 'enum') {
                $valuesForStatus = explode(",", $retVals);

                foreach ($valuesForStatus as $sts) {
                    $stName = strtoupper($column->Field . '_' . str_replace([" ", "-", "\"", "/"], "_", $sts));
                    $this->constants[$tableName][$stName] = $sts;

                    if (is_string($sts)) {
                        $constantsStr.= 'const ' . $stName . '=' . "'$sts'" . ";" . "\n";
                    } else {
                        $constantsStr.= 'const ' . $stName . '=' . "$sts" . ";" . "\n";
                    }
                }
            }
        }
        return $constantsStr;
    }

    /**
     * Generate php code for a model class
     * @param type $tableName
     * @return type
     */
    private function generateContent($tableName) {
        try {
            $modelContent = file_get_contents(__DIR__ . '/templates/model.txt');
            $modelContent = str_replace("@@namespace@@", $this->namespace, $modelContent);
            $modelContent = str_replace("@@modelName@@", $this->getModelName($tableName), $modelContent);
            $modelContent = str_replace("@@tableName@@", $tableName, $modelContent);

            $constantsStr = $this->prepareConstant($tableName);

            $modelContent = str_replace("@@constants@@", $constantsStr, $modelContent);

            $dateColumns = '';
            if (isset($this->dateColumns[$tableName]) && !empty($this->dateColumns[$tableName])) {
                $dateColumns = 'protected $dates=[';
                foreach ($this->dateColumns[$tableName] as $dtsClm) {
                    $dateColumns.="'$dtsClm',";
                }
                $dateColumns = rtrim($dateColumns, ",");
                $dateColumns.='];' . "\n";
            }

            $modelContent = str_replace("@@dateColumns@@", $dateColumns, $modelContent);

            $relationShipsStr = $this->prepareRelationShip($tableName);
            $modelContent = str_replace("@@relationShips@@", $relationShipsStr, $modelContent);

            $scopePlaceholders = $this->prepareScopes($tableName);

            $modelContent = str_replace("@@scopeMethods@@", $scopePlaceholders, $modelContent);

            $modelContent = str_replace("@@propertyDefiner@@", $this->propertyDefiner, $modelContent);

            return $modelContent;
        } catch (\Exception $ex) {
            $this->errors[] = $ex->getMessage();
        }
        return false;
    }

    /**
     * 
     * After complete necessary action its time to create Model class file
     */
    public function make() {
        try {
            foreach ($this->tables as $table) {

                if (in_array($table, $this->pivotTables)) {
                    continue;
                }
                $this->create($table);
            }
        } catch (\Exception $ex) {
            $this->errors[] = $ex->getMessage() . ' on ' . $ex->getLine() . ' in' . $ex->getFile();
        }
    }

    /**
     * Create a Single Model
     * @param type $table
     * @return boolean
     * @throws Exception
     */
    public function create($table) {
        try {
            $signularTable = $this->getModelName($table);
            $fullPath = base_path($this->path) . '/' . $signularTable . '.php';

            if (!file_exists($fullPath)) {
                $modelContent = $this->generateContent($table);
                file_put_contents($fullPath, $modelContent);
                return true;
            }
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode(), $ex);
        }
        return false;
    }

    //public function make()
}
