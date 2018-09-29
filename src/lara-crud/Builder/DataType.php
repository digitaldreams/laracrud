<?php
/**
 * Created for laracrud.
 * User: Angujo Barrack
 * Date: 2018-09-29
 * Time: 1:38 PM
 */

namespace LaraCrud\Builder;


use DbReader\Column;

class DataType
{
    const CHAR       = 'char';
    const VARCHAR    = 'varchar';
    const TINYTEXT   = 'tinytext';
    const TEXT       = 'text';
    const MEDIUMTEXT = 'mediumtext';
    const LONGTEXT   = 'longtext';
    const DATE       = 'date';
    const DATETIME   = 'datetime';
    const TIMESTAMP  = 'timestamp';
    const TIME       = 'time';
    const ENUM       = 'enum';
    const SET        = 'set';
    const TINYINT    = 'tinyint';
    const SMALLINT   = 'smallint';
    const MEDIUMINT  = 'mediumint';
    const INT        = 'int';
    const BIGINT     = 'bigint';
    const FLOAT      = 'float';
    const DOUBLE     = 'double';
    const DECIMAL    = 'decimal';

    protected $string = [
        self::CHAR,
        self::VARCHAR,
        self::TINYTEXT,
        self::TEXT,
        self::MEDIUMTEXT,
        self::LONGTEXT,
    ];
    protected $int    = [
        self::TINYINT,
        self::SMALLINT,
        self::MEDIUMINT,
        self::INT,
        self::BIGINT,
    ];
    protected $bool   = [
        self::TINYINT,
    ];
    protected $column;
    protected $TYPE;

    public function __construct(Column $column)
    {
        $this->column = $column;
    }

    public function cast()
    {
        if (null !== ($t = $this->typeCasted())) return $t;
        if (\in_array($this->column->type(), $this->string, false)) return 'string';
        if (1 === (int)$this->column->length() && 0 === strcmp($this->column->type(), self::TINYINT)) return 'boolean';
        if (\in_array($this->column->type(), $this->int, false)) return 'int';
        return 'mixed';
    }

    private function typeCasted()
    {
        $types = config('laracrud.model.castTypes');
        if (!is_array($types)) return null;
        if (isset($types[$this->column->name()])) return $types[$this->column->name()];
        foreach ($types as $col => $type) {
            $regex = str_ireplace('*', '(.*)', $col);
            if (1 === preg_match('/' . $regex . '/i', $this->column->name())) return $type;
        }
        return null;
    }
}