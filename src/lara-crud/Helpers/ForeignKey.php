<?php

namespace LaraCrud\Helpers;

/**
 * Class ForeignKey.
 *
 * Process a row from information schema
 */
class ForeignKey
{
    use DatabaseHelper;
    use Helper;

    final public const RELATION_BELONGS_TO = 'belongsTo';
    final public const RELATION_HAS_MANY = 'hasMany';
    final public const RELATION_HAS_ONE = 'hasOne';
    final public const RELATION_BELONGS_TO_MANY = 'belongsToMany';

    /**
     * @var \PDO
     */
    protected $db;

    /**
     * @var bool
     */
    public $isPivot = false;

    /**
     * ForeignKey constructor.
     *
     * @param $data
     *
     * @throws \Exception
     * @param \stdClass $data
     */
    public function __construct(/**
     * @var \stdClass [
     *                TABLE_NAME=>
     *                COLUMN_NAME=>
     *                REFERENCED_TABLE_NAME=>
     *                REFERENCED_COLUMN_NAME=>
     *                ]
     */
    protected $data)
    {
        $this->db = (new Connector())->pdo();
        $this->isPivot = $this->isPivot();
    }

    /**
     * Name of the table that hold the foreign key.
     *
     * @return bool
     */
    public function table()
    {
        return $this->data->TABLE_NAME ?? false;
    }

    /**
     * Name of the column that used as foreign key.
     *
     * @return bool
     */
    public function column()
    {
        return $this->data->COLUMN_NAME ?? false;
    }

    /**
     * Name of the Foreign Table name.
     */
    public function foreignTable(): string|bool
    {
        return $this->data->REFERENCED_TABLE_NAME ?? false;
    }

    /**
     * Column name of foreign table that has relation to.
     *
     * @return bool
     */
    public function foreignColumn()
    {
        return $this->data->REFERENCED_COLUMN_NAME ?? false;
    }

    /**
     * Check whether current table is a pivot table or not.
     * For example, user_team is a pivot table. That does not have a primary column.
     * So by running this query we can see whether this table has a primary key called id.
     *
     * @return bool
     */
    public function isPivot()
    {
        $dbName = $this->getDatabaseName();
        $tableName = $this->table();
        $sql = "SELECT COUNT(*) as total FROM  INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                                    WHERE TABLE_SCHEMA='$dbName'
                                     AND TABLE_NAME='$tableName'
                                     AND CONSTRAINT_NAME='PRIMARY'
                                     AND COLUMN_NAME='id'
                                     ";
        $result = $this->db->query($sql)->fetch(\PDO::FETCH_OBJ);

        return $result->total > 0 ? false : true;
    }

    /**
     *  Get Model name based on TABLE_NAME.
     *
     * @return string
     */
    public function modelName()
    {
        $name = '';
        if ($this->isPivot) {
            $name = str_replace([$this->getSingular($this->foreignTable()), '_'], '', $this->table());
        } else {
            $name = $this->table();
        }

        return $this->getModelName($name);
    }

    /**
     * Make Relation array.
     *
     * @return array
     */
    public function relation()
    {
        $relation = [];

        if ($this->isPivot) {
            $relation = [
                'name' => static::RELATION_BELONGS_TO_MANY,
                'foreign_key' => $this->column(),
                'model' => $this->modelName(),
                'other_key' => $this->foreignColumn(),
                'pivotTable' => $this->table(),
            ];
        } else {
            $relation = [
                'name' => static::RELATION_BELONGS_TO,
                'foreign_key' => $this->column(),
                'model' => $this->modelName(),
                'other_key' => $this->foreignColumn(),
            ];
        }

        return $relation;
    }
}
