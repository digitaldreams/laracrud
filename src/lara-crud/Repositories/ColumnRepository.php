<?php

namespace LaraCrud\Repositories;

use DbReader\Column;
use LaraCrud\Contracts\ColumnContract;
use LaraCrud\Contracts\TableContract;
use LaraCrud\Helpers\ForeignKey;

class ColumnRepository implements ColumnContract
{
    /**
     * @var Column;
     */
    protected Column $column;

    /**
     * @var array|\StdClass
     */
    protected $foreignData;

    /**
     * This will use to generate @property .
     *
     * @var string[]
     */
    protected array $phpDataTypes = [
        //MySql Type => PHP native data type
        'varchar' => 'string',
        'text' => 'string',
        'longtext' => 'string',
        'tinytext' => 'string',
        'mediumtext' => 'string',
        'enum' => 'string',
        'tinyint' => 'int',
        'smallint' => 'int',
        'float' => 'int',
        'double' => 'int',
        'decimal' => 'int',
        'bigint' => 'int',
        'json' => 'array',
        'timestamp' => '\Carbon\Carbon',
        'datetime' => '\Carbon\Carbon',
    ];

    /**
     * @var TableContract
     */
    protected $table;

    /**
     * @param mixed                                             $data
     * @param TableRepository|\LaraCrud\Contracts\TableContract $tableRepository
     * @param array                                             $foreignColumn
     *
     * @throws \Exception
     */
    public function __construct($data, TableContract $tableRepository, $foreignColumn = [])
    {
        $this->table = $tableRepository;
        $this->column = new Column($data, $foreignColumn, $tableRepository->getTable());
        $this->foreignData = $foreignColumn;

        $files = $this->table->fileColumns();
        $file = isset($files[$this->column->name()]) ? $files[$this->column->name()] : '';
        $this->column->setFile($file);
    }

    /**
     * @return bool
     */
    public function isPk(): bool
    {
        return $this->column->isPk();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->column->name();
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return $this->column->label();
    }

    /**
     * @return TableContract
     */
    public function table(): TableContract
    {
        return $this->table;
    }

    /**
     * @return bool
     */
    public function isFillable(): bool
    {
        return !$this->column->isProtected();
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return !$this->column->isNull();
    }

    /**
     * @return bool
     */
    public function isNull(): bool
    {
        return $this->column->isNull();
    }

    /**
     * @return bool
     */
    public function isUnique(): bool
    {
        return $this->column->isUnique();
    }

    /**
     * @return string
     */
    public function dataType(): string
    {
        return $this->column->type();
    }

    /**
     * @return string
     */
    public function phpDataType(): string
    {
        return isset($this->phpDataTypes[$this->dataType()]) ? $this->phpDataTypes[$this->dataType()] : $this->dataType();
    }

    /**
     * @return string
     */
    public function inputType(): string
    {
        // TODO: Implement inputType() method.
    }

    /**
     * @return mixed
     */
    public function length()
    {
        return $this->column->length();
    }

    /**
     * @return int|null
     */
    public function order(): ?int
    {
        // TODO: Implement order() method.
    }

    /**
     * @param int $number
     *
     * @return mixed
     */
    public function setOrder(int $number): ColumnContract
    {
        // TODO: Implement setOrder() method.
    }

    /**
     * @return mixed
     */
    public function defaultValue()
    {
        return $this->column->defaultValue();
    }

    /**
     * Whether current column is uploadable image or not.
     *
     * @return string
     */
    public function image(): string
    {
        return $this->column->isFile();
    }

    /**
     * Whether current column is uploadable file or not?
     *
     * @return string
     */
    public function file(): string
    {
        return $this->column->isFile();
    }

    /**
     * For Enum data type there are some predefined data set.
     *
     * @return array|null
     */
    public function options(): ?array
    {
        return $this->column->options();
    }

    /**
     * @return bool
     */
    public function isForeign(): bool
    {
        return $this->column->isForeign();
    }

    /**
     * @return \LaraCrud\Helpers\ForeignKey|null
     */
    public function foreignKey()
    {
        if ($this->column->isForeign()) {
            return new ForeignKey($this->foreignData);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function validationRules(): array
    {
        $rules = [];
        if (!$this->isNull()) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        if ($this->isUnique()) {
            $rules[] = sprintf("Rule::unique('%s','%s')", $this->table->name(), $this->name());
        }
        if ($this->isForeign()) {
            $rules[] = sprintf("Rule::exists('%s','%s')", $this->column->foreignTable(), $this->column->foreignColumn());
        }
        if ('enum' == $this->dataType()) {
            $rules[] = 'in:' . implode(',', $this->options());
        } elseif ($this->file()) {
            $rules[] = 'file';
        } elseif (in_array($this->dataType(), ['varchar'])) {
            $rules[] = 'max:' . $this->length();
        } elseif ('tinyint' == $this->dataType() && 1 == $this->length()) {
            $rules[] = 'boolean';
        } elseif (in_array($this->dataType(), ['smallint', 'int', 'mediumint', 'bigint', 'decimal', 'float', 'double'])) {
            $rules[] = 'numeric';
        } elseif (in_array($this->dataType(), ['date', 'time', 'datetime', 'timestamp'])) {
            $rules[] = 'date';
        }

        if (in_array($this->dataType(), ['text', 'tinytext', 'mediumtext', 'longtext'])) {
            $rules[] = 'string';
        }

        return $rules;
    }
}
