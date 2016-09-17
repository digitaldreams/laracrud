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
    /**
     * It will be added as comments before the class.
     * It will auto suggest property name and its datatype while you are trying to insert values
     * 
     * @var string
     */
    public $propertyDefiner = '';

    /**
     * Model Namespace
     * @var string
     */
    public $namespace = 'App';

    /**
     * Model Path. Where to save Model class file.
     * @var string
     */
    public $path = 'app';

    /**
     *
     * @var string
     */
    public $modelName = '';

    /**
     * Get table name. It may be an array or string.
     * Does all necessary work before start making Model.
     * @param string|array $table
     */
    public function __construct($table = '', $name = '')
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
        $this->path      = $this->getConfig('modelpath');
        $this->namespace = $this->getConfig('modelNameSpace');

        if (!empty($name)) {
            if (strpos($name, "/", 1) !== FALSE) {
                $narr            = explode("/", trim($name, "/"));
                $this->modelName = $this->getModelName(array_pop($narr));

                foreach ($narr as $path) {
                    $this->namespace.='\\'.ucfirst($path);
                    $this->path.='/'.ucfirst($path);
                }
                //   $sname=  str_replace("/","\\",$name);
            } else {
                $this->modelName = $this->getModelName($name);
            }
        }
        $this->loadDetails();
        $this->columnDataTypes();
        $this->findPivotTables();
        $this->prepareRelation();
        //If model path does not exists then create model path.
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
        $methodDefiner     = '';
        if (isset($this->tableColumns[$tableName])) {
            foreach ($this->tableColumns[$tableName] as $column) {
                $type = $column->Type;
                if (empty($column->Field)) {
                    continue;
                }
                //There are some column type with () and e.g. int(10) where type is int and length is 10. So we need to get 10
                if (strpos($type, "(")) {
                    $type = substr($column->Type, 0, strpos($column->Type, "("));
                }
                $propertyDefiner.='@property '.$type.' $'.$column->Field.' '.str_replace("_",
                        " ", $column->Field)."\n";
                $methodDefiner.='@method \Illuminate\Database\Eloquent\Builder '.lcfirst($this->getModelName($column->Field)).'('.$type.' $'.$column->Field.')'.str_replace("_",
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
        $propertyDefiner.=$methodDefiner;
        $this->propertyDefiner = $propertyDefiner;
        return $scopePlaceholders;
    }

    /**
     * Prepare Model Relationship based on Foreign key
     * @param string $tableName
     * @return string
     */
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

    /**
     * There are some enum field.There have some predefined values. E.g. status('active','inactive','paused');
     * Here we will create constant
     * STATUS_ACTIVE, STATUS_INACTIVE, STATUS_PAUSED.
     * @param string $tableName
     * @return string
     */
    protected function prepareConstant($tableName)
    {
        $constantsStr = '';

        foreach ($this->tableColumns[$tableName] as $column) {
            $type = $column->Type;

            $dataType = substr($type, 0, strpos($type, "("));

            $retVals = $this->extractRulesFromType($type);
            //Check if it is a data time column. If so then add it to $protected $dates=[]
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
                $this->getNewModelName($tableName), $modelContent);
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

    /**
     * set*Attribute($value) method are used to filter value before insert to table.
     * This metod are automatically generated by Laravel Model.
     * So in this case we will convert our date and string field to its appropriate value.
     * for example for a date field database support only Y-m-d format but user can type m/d/Y format.
     *  So it need to convert before save into table
     * 
     * Same is true for get*Attribute($value) which is called before we get value to show.
     * So its very hard to read Y-m-d data format.
     *  So we will convert this data to m/d/Y format and display it to user.
     * 
     * @param string $tableName
     * @return string
     */
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
            $signularTable = $this->getNewModelName($table);
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

    /**
     * To Generate protected $fillable=[]
     * To stop mass assignment laravel have this nice feature. By defining which property will be
     * fillable we can save unexpected error
     * 
     * @param string $table
     * @return string
     */
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

    /**
     * For type casting laravel model have protected $casts=[]
     * We can type casting before save to table
     * e.g. In a boolean table we can get value yes,1, true from user.
     *  But we need to make it 1 or true not yes.
     * 
     * @param type $table
     * @return string
     */
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

    /**
     * Get fully qualified name of the Model
     * @param string $table
     * @return string
     */
    public function getFullModelName($table)
    {
        $modelName = $this->getModelName($table);
        return '\\'.$this->namespace.'\\'.$modelName;
    }

    //public function make()
    public function getNewModelName($table)
    {
        if (!empty($this->modelName)) {
            return $this->modelName;
        }
        return $this->getModelName($table);
    }
}