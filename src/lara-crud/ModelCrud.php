<?php

namespace LaraCrud;

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
class ModelCrud extends LaraCrud
{
    public $propertyDefiner = '';
    public $namespace       = 'App\Models';
    public $path            = 'app/Models';

    public function __construct($table = '')
    {
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
        $this->findPivotTables();
        $this->prepareRelation();

        if (!file_exists(base_path($this->path))) {
            mkdir(base_path($this->path));
        }
    }

    /**
     * Generate scopeMethods for a Model
     */
    protected function prepareScopes($tableName)
    {

        $scopePlaceholders = '';
        $propertyDefiner   = '';
        if (isset($this->tableColumns[$tableName])) {
            foreach ($this->tableColumns[$tableName] as $column) {
                $type = $column->Type;
                if (empty($column->Field)) {
                    continue;
                }
                if (strpos($type, "(")) {
                    $type = substr($column->Type, 0, strpos($column->Type, "("));
                }
                $propertyDefiner.='@property '.$type.' $'.$column->Field.' '.str_replace("_",
                        " ", $column->Field)."\n";
                $scopeTemplateStr = $this->getTempFile('scope.txt');
                $scopeMethodName  = ucfirst(camel_case($column->Field));
                $scopeTemplateStr = str_replace("@@methodName@@",
                    $scopeMethodName, $scopeTemplateStr);
                $scopeTemplateStr = str_replace("@@fielName@@", $column->Field,
                    $scopeTemplateStr);
                $scopePlaceholders.=$scopeTemplateStr."\n\n";
            }
        }
        $this->propertyDefiner = $propertyDefiner;
        return $scopePlaceholders;
    }

    protected function prepareRelationShip($tableName)
    {
        $relationShipsStr = '';

        if (isset($this->finalRelationShips[$tableName]) && !empty($this->finalRelationShips[$tableName])) {
            $params = '';

            foreach ($this->finalRelationShips[$tableName] as $rls) {
                if (empty($rls['model'])) {
                    continue;
                }
                $newCloneRelation = $this->getTempFile('relationship.txt');
                $paramsNt         = '';
                $newCloneRelation = str_replace("@@methodName@@",
                    lcfirst($rls['model']), $newCloneRelation);
                $newCloneRelation = str_replace("@@modelName@@", $rls['model'],
                    $newCloneRelation);
                $newCloneRelation = str_replace("@@relationShip@@",
                    $rls['name'], $newCloneRelation);
                $newCloneRelation = str_replace("@@namespace@@",
                    $this->namespace, $newCloneRelation);

                if (isset($rls['pivotTable']) && $rls['name'] = 'belongsToMany') {
                    $paramsNt = ","."'".$rls['pivotTable']."'";
                } else {
                    $paramsNt = ","."'".$rls['foreign_key']."'";
                }

                $newCloneRelation = str_replace("@@params@@", $paramsNt,
                    $newCloneRelation);

                $relationShipsStr.=$newCloneRelation."\n\n";
            }
        }
        return $relationShipsStr;
    }

    protected function prepareConstant($tableName)
    {
        $constantsStr = '';

        foreach ($this->tableColumns[$tableName] as $column) {
            $type = $column->Type;

            $dataType = substr($type, 0, strpos($type, "("));

            $retVals = $this->extractRulesFromType($type);

            if (in_array($column->Type,
                    ['time', 'date', 'datetime', 'timestamp']) && !in_array($column->Field,
                    $this->systemColumns)) {
                $this->dateColumns[$tableName][] = $column->Field;
            }

            if (strpos($type, "(") && $dataType == 'enum') {
                $valuesForStatus = explode(",", $retVals);

                foreach ($valuesForStatus as $sts) {
                    $stName                               = strtoupper($column->Field.'_'.str_replace([" ",
                            "-", "\"", "/"], "_", $sts));
                    $this->constants[$tableName][$stName] = $sts;

                    if (is_string($sts)) {
                        $constantsStr.= 'const '.$stName.'='."'$sts'".";"."\n";
                    } else {
                        $constantsStr.= 'const '.$stName.'='."$sts".";"."\n";
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
    private function generateContent($tableName)
    {
        try {
            $modelContent = $this->getTempFile('model.txt');
            $modelContent = str_replace("@@namespace@@", $this->namespace,
                $modelContent);
            $modelContent = str_replace("@@modelName@@",
                $this->getModelName($tableName), $modelContent);
            $modelContent = str_replace("@@tableName@@", $tableName,
                $modelContent);

            $constantsStr = $this->prepareConstant($tableName);

            $modelContent = str_replace("@@constants@@", $constantsStr,
                $modelContent);

            $dateColumns = '';
            if (isset($this->dateColumns[$tableName]) && !empty($this->dateColumns[$tableName])) {
                $dateColumns = 'protected $dates=[';
                foreach ($this->dateColumns[$tableName] as $dtsClm) {
                    $dateColumns.="'$dtsClm',";
                }
                $dateColumns = rtrim($dateColumns, ",");
                $dateColumns.='];'."\n";
            }

            $modelContent = str_replace("@@dateColumns@@", $dateColumns,
                $modelContent);

            $relationShipsStr = $this->prepareRelationShip($tableName);
            $modelContent     = str_replace("@@relationShips@@",
                $relationShipsStr, $modelContent);

            $scopePlaceholders = $this->prepareScopes($tableName);

            $modelContent = str_replace("@@scopeMethods@@", $scopePlaceholders,
                $modelContent);

            $modelContent     = str_replace("@@propertyDefiner@@",
                $this->propertyDefiner, $modelContent);
            $attributeMethods = $this->attributeGenerator($tableName);
            $modelContent     = str_replace("@@attributeMethods@@",
                $attributeMethods, $modelContent);

            $fillableContent = $this->generateFillable($tableName);
            $modelContent    = str_replace("@@fillable@@", $fillableContent,
                $modelContent);

            $castsContent = $this->generateCast($tableName);
            $modelContent = str_replace("@@casts@@", $castsContent,
                $modelContent);


            return $modelContent;
        } catch (\Exception $ex) {
            $this->errors[] = $ex->getMessage();
        }
        return false;
    }

    public function attributeGenerator($tableName)
    {
        $retCode = '';
        if (isset($this->columnsDataType[$tableName])) {
            foreach ($this->columnsDataType[$tableName] as $columnName => $type) {

                if (in_array($columnName, $this->systemColumns)) {
                    continue;
                }
                $temp  = '';
                $label = str_replace(" ", "",
                    ucwords(str_replace("_", " ", $columnName)));

                if (in_array($type, ['time', 'date', 'datetime', 'timestamp'])) {

                    $setDateFormat = isset($this->setDateFormat[$type]) ? $this->setDateFormat[$type]
                            : "Y-m-d";
                    $getDateFormat = isset($this->getDateFormat[$type]) ? $this->getDateFormat[$type]
                            : "Y-m-d";

                    $tempSetDate = $this->getTempFile('setAttributeDate.txt');
                    $tempSetDate = str_replace("@@format@@", $setDateFormat,
                        $tempSetDate);
                    $tempSetDate = str_replace("@@columnLabel@@", $label,
                        $tempSetDate);
                    $tempSetDate = str_replace("@@column@@", $columnName,
                        $tempSetDate);
                    $retCode.=$tempSetDate;

                    $tempGetDate = $this->getTempFile('getAttributeDate.txt');
                    $tempGetDate = str_replace("@@format@@", $getDateFormat,
                        $tempGetDate);
                    $tempGetDate = str_replace("@@columnLabel@@", $label,
                        $tempGetDate);
                    $retCode.=$tempGetDate;
                } elseif (in_array($type,
                        ['varchar', 'text', 'tinytext', 'bigtext'])) {

                    $tempSetText = $this->getTempFile('setAttributeText.txt');
                    $tempSetText = str_replace("@@column@@", $columnName,
                        $tempSetText);
                    $tempSetText = str_replace("@@columnLabel@@", $label,
                        $tempSetText);
                    $retCode.=$tempSetText;
                }
            }
        }
        return $retCode;
    }

    /**
     * 
     * After complete necessary action its time to create Model class file
     */
    public function make()
    {
        try {
            foreach ($this->tables as $table) {

                if (in_array($table, $this->pivotTables)) {
                    continue;
                }
                $this->create($table);
            }
        } catch (\Exception $ex) {
            $this->errors[] = $ex->getMessage().' on '.$ex->getLine().' in'.$ex->getFile();
        }
    }

    /**
     * Create a Single Model
     * @param type $table
     * @return boolean
     * @throws Exception
     */
    public function create($table)
    {
        try {
            $signularTable = $this->getModelName($table);
            $fullPath      = base_path($this->path).'/'.$signularTable.'.php';

            if (!file_exists($fullPath)) {
                $modelContent = $this->generateContent($table);
                $this->saveFile($fullPath, $modelContent);
                return true;
            }
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode(), $ex);
        }
        return false;
    }

    public function generateFillable($table)
    {
        $fillable = '';
        $columns  = $this->columnsDataType;

        if (isset($columns[$table])) {
            $keys = array_keys($columns[$table]);
            foreach ($keys as $key) {
                if ($key == 'id') {
                    continue;
                }
                if (!in_array($key, $this->systemColumns)) {
                    $fillable.="'".$key."',";
                }
            }
        }
        return $fillable;
    }

    public function generateCast($table)
    {
        $cast        = '';
        $columns     = $this->columnsDataType;
        $converTypes = [
            'varchar' => 'string',
            'boolean' => 'bool',
            'enum' => 'string',
            'int' => 'int',
            'double' => 'double',
            'bigint' => 'int',
            'tinyint' => 'int'
        ];
        if (isset($columns[$table])) {
            $keys = array_keys($columns[$table]);
            foreach ($columns[$table] as $key => $type) {
                if ($key == 'id') {
                    continue;
                }
                if (isset($converTypes[$type])) {
                    $cast.="'".$key."'=>'".$converTypes[$type]."',";
                }
            }
        }
        return $cast;
    }

    public function getFullModelName($table)
    {
        $modelName = $this->getModelName($table);
        return '\\'.$this->namespace.'\\'.$modelName;
    }
    //public function make()
}