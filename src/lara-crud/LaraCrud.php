<?php

namespace LaraCrud;

use Illuminate\Support\Facades\DB;

/*
 * Model, Request, Controller, View are most used and important part in each and every laravel 
 * 
 * There are some common methods and property are used in this classes 
 * like in model scope.*(), relation() and $table, $dates etc. 
 * If we are able to generate this method based on database table structure then 
 * we do not need to write common methods again and again.
 * 
 * Our purpose is to generate common functionality by this Library
 * 
 * @author Tuhin
 */

class LaraCrud
{
    const RELATION_BELONGS_TO      = 'belongsTo';
    const RELATION_HAS_MANY        = 'hasMany';
    const RELATION_HAS_ONE         = 'hasOne';
    const RELATION_BELONGS_TO_MANY = 'belongsToMany';

    /**
     * Parent Namespace of Model
     * @var type 
     */
    public $namespace = '';

    /**
     * Folder path of the Model
     * @var type 
     */
    public $path = '';

    /**
     * Pivot Table that are used for maintaining Relationships only
     * @var array
     */
    public $pivotTables = ['migrations'];

    /**
     * List of the Table name
     * @var array
     */
    protected $tables = [];

    /**
     * Main table which will be created as Model, View etc
     * @var type 
     */
    protected $mainTable = '';

    /**
     * Table Column Details
     * @var array
     *
     * [
     *  tableName=>[
     *          [
     *                [Field] => id
      [Type] => int(10) unsigned
      [Null] => NO
      [Key] => PRI
      [Default] =>
      [Extra] => auto_increment
     *          ]
     *      ]
     * ]
     */
    public $tableColumns = [];

    /**
     * Columns used by the Laravel Framework internally
     * e.g. updated_at, created_at, deleted_at
     * @var string
     */
    public $systemColumns = [];

    /**
     * Columns which will be not be searchable and is not viewable to public
     * @var type 
     */
    protected $protectedColumns = [];
    public $columnsDataType     = [];

    /**
     * Date Format of the Column if column is whthin date,datetime,time,timestamp.
     * For example birth_date value column is typically date so its here in getDateFormat[date]=>'m/d/Y'
     * So when getBirthDateAttribute() method return formatted date this formate will be used.
     * @var string.
     *
     *
     */
    public $getDateFormat = [];

    /**
     * Date Time Column may have more than one accpeted values but system will insert only one format.
     *
     * For example. 12 November 2016, November 12 2016, 11/12/2016 all there are valid date and user may input any of this.
     * But system will save only 2016-11-12 (Y-m-d) format.
     *
     * @var type 
     */
    public $setDateFormat = [];

    /**
     * List of indexes
     * @var type 
     */
    protected $indexes = [];

    /**
     * Eloquent Relationship
     * this will hold raw relationship from database
     * @var type 
     */
    protected $relationships = [];

    /**
     * After make logical decesion here we hold eloquent relationship
     * @var array 
     */
    public $finalRelationShips = [];

    /**
     * Will hold the enum values form tabase table. Then it will create constant to model class
     * @var array 
     */
    public $constants = [];

    /**
     * Any date/ DateTime datatype column's name will be saved in here.
     *  E.g birth_date in users table which data type is date will be automatically push in here
     * @var type 
     */
    public $dateColumns = [];

    /**
     * This is an associative array. Where index will be table column data type where value will be laravel validation name
     * @var array 
     */
    protected $validationNames = [
        'int' => 'integer',
        'bigint' => 'integer',
        'varchar' => 'string',
        'timestamp' => 'date',
        'time' => 'date',
        'date' => 'date',
        'datetime' => 'date',
        'enum' => 'in',
        'tinyint' => 'boolean',
    ];

    /**
     * Request Validation Rules
     * @var array
     */
    public $rules = [];

    /**
     * This will hold any foreign keys in a table. 
     * Like in profile table user_id is a foreign key which indicate the users table
     * @var array 
     */
    public $foreignKeys = [];

    /**
     * Error Bag
     * @var type 
     */
    public $errors = [];

    /**
     * Load default Configuration
     * @var type 
     */
    protected $config = [];

    /**
     * Get list of table name from current database
     * @return array 
     */
    public function __construct()
    {
        $this->config           = include __DIR__.'/../config/laracrud.php';
        $this->getDateFormat    = $this->getConfig('getDateFormat');
        $this->setDateFormat    = $this->getConfig('setDateFormat');
        $this->systemColumns    = $this->getConfig('systemColumns');
        $this->protectedColumns = $this->getConfig('protectedColumns');
        $this->pivotTables      = $this->getConfig('pivotTables');
    }

    /**
     * Get all the tables name from the database
     * @return array
     */
    public function getTableList()
    {

        $this->tables = static::getTablesName();
        return $this->tables;
    }

    protected static function getTablesName()
    {
        $tableNames = [];
        $result     = DB::select('SHOW TABLES');
        foreach ($result as $tb) {
            $tb           = (array) $tb;
            $tableName    = array_values($tb);
            $tableNames[] = array_shift($tableName);
        }
        return $tableNames;
    }

    /**
     * Get columns and its property of a table
     * @return boolean
     */
    public function loadDetails()
    {
        try {
            foreach ($this->tables as $tableName) {
                $tableDetails = DB::select("EXPLAIN ".$tableName);
                $indexes      = DB::select('SHOW INDEXES FROM '.$tableName);

                if (!empty($tableDetails)) {
                    $this->tableColumns[$tableName] = $tableDetails;
                }

                if (!empty($indexes)) {
                    $this->indexes[$tableName] = $indexes;
                }
            }
            $this->relationships = $this->getRelationShip();
            return true;
        } catch (\Exception $ex) {
            $this->errors[] = $ex->getMessage().$ex->getLine().$ex->getFile();
        }
    }

    /**
     * This is a helper method. It is used in array_filter
     * @param type $index
     * @return boolean
     */
    public function filterIndex($index)
    {
        if (isset($index->REFERENCED_TABLE_NAME) && !empty($index->REFERENCED_TABLE_NAME)) {
            return $index;
        } else {
            return false;
        }
    }

    /**
     * In mysql foreign relationship is stored in INFORMATION_SCHEMA database. 
     * We will get foreign key information by table name
     * 
     * @param string $tableName Get all foreign relation for a table
     */
    public function getRelationShip($tableName = '')
    {
        $tableName = !empty($tableName) ? $tableName : $this->getTablesName();
        $dbName    = env('DB_DATABASE');

        $sql = "SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
                                    FROM  INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                                    WHERE TABLE_SCHEMA='$dbName' ";
        if (is_array($tableName)) {
            $makeOptions = '';
            foreach ($tableName as $tb) {
                $makeOptions.="'".$tb."',";
            }
            $makeOptions = rtrim($makeOptions, ",");
            $sql.= " AND TABLE_NAME IN ($makeOptions)";
        } else {
            $sql.= " AND TABLE_NAME ='$tableName'";
        }

        $sql.=" AND REFERENCED_TABLE_NAME IS NOT NULL";

        $relationShips = DB::select($sql);
        return $relationShips;
    }

    /**
     * Fill up final Relationship based on logic
     */
    public function prepareRelation()
    {
        $uniqueRelationShips = [];
        $relationShipsArr    = $this->relationships;
        foreach ($relationShipsArr as $rel) {
            $uniqueRelationShips[$rel->CONSTRAINT_NAME] = $rel;
        }
        $this->relationships = $uniqueRelationShips;

        foreach ($uniqueRelationShips as $relation) {
            $this->foreignKeys[$relation->TABLE_NAME]['keys'][]                      = $relation->COLUMN_NAME;
            $this->foreignKeys[$relation->TABLE_NAME]['rel'][$relation->COLUMN_NAME] = $relation;

            //If current table is a pivot table then it holds many to many relationship
            if (in_array($relation->TABLE_NAME, $this->pivotTables)) {
                $modelName                  = $relation->TABLE_NAME;
                $singularReferenceTableName = $this->getSingular($relation->REFERENCED_TABLE_NAME);

                $modelName                                                    = str_replace([$singularReferenceTableName,
                    "_"], "", $relation->TABLE_NAME);
                $this->finalRelationShips[$relation->REFERENCED_TABLE_NAME][] = [
                    'name' => static::RELATION_BELONGS_TO_MANY,
                    'foreign_key' => $relation->COLUMN_NAME,
                    'model' => $this->getModelName($modelName),
                    'other_key' => $relation->REFERENCED_COLUMN_NAME,
                    'pivotTable' => $relation->TABLE_NAME
                ];
            } else {
                $this->finalRelationShips[$relation->TABLE_NAME][]            = [
                    'name' => static::RELATION_BELONGS_TO,
                    'foreign_key' => $relation->COLUMN_NAME,
                    'model' => $this->getModelName($relation->REFERENCED_TABLE_NAME),
                    'other_key' => $relation->REFERENCED_COLUMN_NAME
                ];
                $this->finalRelationShips[$relation->REFERENCED_TABLE_NAME][] = [
                    'name' => static::RELATION_HAS_MANY,
                    'foreign_key' => $relation->COLUMN_NAME,
                    'model' => $this->getModelName($relation->TABLE_NAME),
                    'other_key' => $relation->REFERENCED_COLUMN_NAME
                ];
            }
        }
    }

    /**
     * Generally laravel use plural version of model name  as table name.
     * For example products table will be Product as Model name
     * @param string $words
     * @return string
     */
    public function getSingular($words)
    {
        $retSingular = '';
        return str_singular($words);
    }

    protected function extractRulesFromType($type)
    {
        $retType     = '';
        $dataType    = substr($type, 0, strpos($type, "("));
        $values      = substr($type, strpos($type, "("), strrpos($type, ")"));
        $cleanvalues = str_replace(["(", ")", "'"], "", $values);

        if (trim($dataType) == 'enum') {
            $retType = $cleanvalues;
        } else {
            $retType = filter_var($cleanvalues, FILTER_SANITIZE_NUMBER_INT);
        }
        return $retType;
    }

    /**
     * Convert table name to Laravel Standard Model Name
     * For example users table become User.
     * Plural to singular and snakeCase to camelCase
     *
     * @param type $name
     * @return type
     */
    public function getModelName($name)
    {
        $name = $this->getSingular($name);
        return ucfirst(camel_case($name));
    }

    /**
     * Pass relative path of the file and get absolute file path.
     * All the internal used template are stored in the templates folder
     * So view/controller.txt will return full path.
     * @param type $file
     * @return string
     */
    public function getTempFile($file)
    {
        try {

            $path = __DIR__."/templates/$file";
            if (file_exists($path)) {
                return file_get_contents($path);
            }
            return '';
        } catch (\Exception $ex) {
            return '';
        }
    }

    /**
     * This will get columns data Type from tableColumns array and store in columnsDataType
     * for future use. E.g. making Request Rules, Model mutators and accessors and migration column method
     * 
     */
    public function columnDataTypes()
    {
        foreach ($this->tableColumns as $tname => $tableColumns) {
            foreach ($tableColumns as $column) {
                $type = $column->Type;
                if (strpos($type, "(")) {
                    $type = substr($type, 0, strpos($type, "("));
                }
                $this->columnsDataType[$tname][$column->Field] = $type;
            }
        }
    }

    /**
     * 
     * @param url $filePath Absolute path of the file where to save. E.g. for Model ..../app/User.php
     * @param string $contents content of the file
     * @return boolean
     * @throws \Exception
     */
    public function saveFile($filePath, $contents)
    {
        try {
            $fileObject = new \SplFileObject($filePath, 'w+');
            $fileObject->fwrite($contents);
            return true;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    /**
     * Find out pivot table for better relationship logic
     */
    public function findPivotTables()
    {
        $tablesWithoutPrimaryKey = $this->pivotTables;
        //  $lc=new static;
        // $lc->getTableList();
        // $lc->loadDetails();
        foreach ($this->tableColumns as $tableName => $columns) {
            $primaryKey = [];
            foreach ($columns as $column) {

                if ($column->Key == 'PRI') {
                    $primaryKey[] = $column->Field;
                }
            }
            if (empty($primaryKey)) {
                $tablesWithoutPrimaryKey[] = $tableName;
            }
        }
        $this->pivotTables = array_unique($tablesWithoutPrimaryKey);
    }

    /**
     * If table name mistyped and then tell user that table not found and show him a list of table.
     *
     * @param type $table
     * @return boolean
     * @throws \Exception
     */
    public static function checkMissingTable($table)
    {
        try {
            if (!is_array($table)) {
                $insertAbleTable = [$table];
            }

            $availableTables = static::getTablesName();
            $missingTable    = array_diff($insertAbleTable, $availableTables);

            if (!empty($missingTable)) {
                $message = implode(",", $missingTable).' tables not found in '.implode("\n", $availableTables);
                throw new \Exception($message);
            }
            return true;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * Get value from configuration
     * @param string $key
     * @return string
     */
    public function getConfig($key)
    {
        $default = isset($this->config[$key]) ? $this->config[$key] : null;
        return config('laracrud.'.$key, $default);
    }

    /**
     * Path to Namespace
     * @param type $path
     * @return string Valid namespace
     */
    public function pathToNs($path)
    {
        $ns = str_replace("/", "\\", $path);
        $ns = str_replace("app", "App", $ns);
        if (substr_compare($ns, "\\", 0, 1) !== 0) {
            return "\\".$ns;
        }
        return $ns;
    }
}