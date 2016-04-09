<?php

namespace App\Libs;

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

class LaraCrud {

    const RELATION_BELONGS_TO = 'belongsTo';
    const RELATION_HAS_MANY = 'hasMany';
    const RELATION_HAS_ONE = 'hasOne';
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
    public $pivotTables = ['consumer_business', 'migrations'];

    /**
     * List of the Table name
     * @var array
     */
    protected $tables = [];

    /**
     * Table Column Details
     * @var array 
     */
    public $tableColumns = [];
    public $systemColumns = ['created_at', 'updated_at', 'deleted_at'];
    public $columnsDataType = [];
    public $getDateFormat = [
        'time' => 'h:i A',
        'date' => 'm/d/Y',
        'datetime' => 'm/d/Y h:i A',
        'timestamp' => 'm/d/Y h:i A'
    ];
    public $setDateFormat = [
        'time' => 'H:i:s',
        'date' => 'Y-m-d',
        'datetime' => 'Y-m-d H:i:s',
        'timestamp' => 'Y-m-d H:i:s'
    ];

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
     * Get list of table name from current database
     * @return array 
     */
    public function getTableList() {
        $tableNames = [];
        $result = DB::select('SHOW TABLES');
        foreach ($result as $tb) {
            $tb = (array) $tb;
            $tableName = array_values($tb);
            $tableNames[] = array_shift($tableName);
        }
        $this->tables = $tableNames;
    }

    /**
     * Get columns and its property of a table
     * @return boolean
     */
    public function loadDetails() {
        try {
            foreach ($this->tables as $tableName) {
                $tableDetails = DB::select("EXPLAIN " . $tableName);
                $indexes = DB::select('SHOW INDEXES FROM ' . $tableName);
                $relationShips = $this->getRelationShip($tableName);

                if (!empty($tableDetails)) {
                    $this->tableColumns[$tableName] = $tableDetails;
                }

                if (!empty($indexes)) {
                    $this->indexes[$tableName] = $indexes;
                }

                $filteredRelationship = array_filter($relationShips, array($this, 'filterIndex'));

                if (!empty($filteredRelationship)) {
                    $this->relationships = array_merge($this->relationships, $filteredRelationship);
                }
            }
            return true;
        } catch (\Exception $ex) {
            $this->errors[] = $ex->getMessage() . $ex->getLine() . $ex->getFile();
        }
    }

    /**
     * This is a helper method. It is used in array_filter
     * @param type $index
     * @return boolean
     */
    public function filterIndex($index) {
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
    public function getRelationShip($tableName) {
        $relationShips = DB::select("SELECT TABLE_NAME,COLUMN_NAME,CONSTRAINT_NAME, REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
                                    FROM  INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                                    WHERE  TABLE_NAME ='$tableName'");
        return $relationShips;
    }

    /**
     * Fill up final Relationship based on logic
     */
    public function prepareRelation() {
        $uniqueRelationShips = [];
        $relationShipsArr = $this->relationships;
        foreach ($relationShipsArr as $rel) {
            $uniqueRelationShips[$rel->CONSTRAINT_NAME] = $rel;
        }
        $this->relationships = $uniqueRelationShips;

        foreach ($uniqueRelationShips as $relation) {
            $this->foreignKeys[$relation->TABLE_NAME]['keys'][] = $relation->COLUMN_NAME;
            $this->foreignKeys[$relation->TABLE_NAME]['rel'][$relation->COLUMN_NAME] = $relation;

            //If current table is a pivot table then it holds many to many relationship
            if (in_array($relation->TABLE_NAME, $this->pivotTables)) {
                $modelName = $relation->TABLE_NAME;
                $singularReferenceTableName = $this->getSingular($relation->REFERENCED_TABLE_NAME);

                $modelName = str_replace([$singularReferenceTableName, "_"], "", $relation->TABLE_NAME);
                $this->finalRelationShips[$relation->REFERENCED_TABLE_NAME][] = [
                    'name' => static::RELATION_BELONGS_TO_MANY,
                    'foreign_key' => $relation->COLUMN_NAME,
                    'model' => $this->getModelName($modelName),
                    'other_key' => $relation->REFERENCED_COLUMN_NAME,
                    'pivotTable' => $relation->TABLE_NAME
                ];
            } else {
                $this->finalRelationShips[$relation->TABLE_NAME][] = [
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
    public function getSingular($words) {
        $retSingular = '';
        return $words;
        $pluralWordSyntex = ['s', 'x', 'z', 'ch', 'sh'];
        if (strripos($words, "es")) {
            $singularWord = substr($words, 0, strripos($words, "es"));
            $singularWordLastWord = substr($singularWord, -1);

            $singularWordLastTwoWord = substr($singularWord, -2);

            if (in_array($singularWordLastWord, $pluralWordSyntex) || in_array($singularWordLastTwoWord, $pluralWordSyntex)) {
                $retSingular = $singularWord;
            } elseif ($singularWordLastWord == 'i') {
                $withoutY = substr($singularWord, 0, strripos($words, "i"));
                $retSingular = $withoutY . "y";
            } else {
                $retSingular = substr($words, 0, strripos($words, "s"));
            }
        } elseif (strripos($words, "s")) {
            /*
              $lastCharacter = substr($words, -1);
              if ($lastCharacter == 's') {
              $retSingular = substr($words, 0, strripos($words, "s"));
              } else {
              $retSingular = $words;
              }
             * 
             */
        } else {
            
        }
        return $retSingular;
    }

    protected function extractRulesFromType($type) {
        $retType = '';
        $dataType = substr($type, 0, strpos($type, "("));
        $values = substr($type, strpos($type, "("), strrpos($type, ")"));
        $cleanvalues = str_replace(["(", ")", "'"], "", $values);

        if (trim($dataType) == 'enum') {
            $retType = $cleanvalues;
        } else {
            $retType = filter_var($cleanvalues, FILTER_SANITIZE_NUMBER_INT);
        }
        return $retType;
    }

    public function getModelName($name) {
        $name = $this->getSingular($name);
        return ucfirst(camel_case($name));
    }

    public function getTempFile($file) {
        try {

            $path = __DIR__ . "/templates/$file";
            if (file_exists($path)) {
                return file_get_contents($path);
            }
            return '';
        } catch (\Exception $ex) {
            return '';
        }
    }

    public function columnDataTypes() {
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

    public function saveFile($filePath, $contents) {
        try {
            $fileObject = new \SplFileObject($filePath, 'w+');
            $fileObject->fwrite($contents);
            return true;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode(), $ex);
        }
    }

}
