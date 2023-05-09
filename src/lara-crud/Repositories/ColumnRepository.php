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
        'char' => 'string',
        'varchar' => 'string',
        'text' => 'string',
        'longtext' => 'string',
        'tinytext' => 'string',
        'mediumtext' => 'string',
        'enum' => 'string',
        'tinyint' => 'int',
        'tinyint unsigned' => 'int',
        'smallint' => 'int',
        'smallint unsigned ' => 'int',
        'int unsigned' => 'int',
        'float' => 'int',
        'float unsigned' => 'int',
        'double' => 'int',
        'double unsigned' => 'int',
        'decimal' => 'int',
        'bigint' => 'int',
        'bigint unsigned' => 'int',
        'json' => 'array',
        'timestamp' => '\\' . \Carbon\Carbon::class,
        'datetime' => '\\' . \Carbon\Carbon::class,
    ];

    /**
     * @var TableContract
     */
    protected $table;

    /**
     * @param TableRepository|\LaraCrud\Contracts\TableContract $tableContract
     * @param array                                             $foreignColumn
     * @throws \Exception
     */
    public function __construct(mixed $data, TableContract $tableContract, $foreignColumn = [])
    {
        $this->table = $tableContract;
        $this->column = new Column($data, $foreignColumn, $tableContract->getTable());
        $this->foreignData = $foreignColumn;

        $files = $this->table->fileColumns();
        $file = $files[$this->column->name()] ?? '';
        $this->column->setFile($file);
    }

    public function isPk(): bool
    {
        return $this->column->isPk();
    }

    public function name(): string
    {
        return $this->column->name();
    }

    public function label(): string
    {
        return $this->column->label();
    }

    public function table(): TableContract
    {
        return $this->table;
    }

    public function isFillable(): bool
    {
        return ! $this->column->isProtected();
    }

    public function isRequired(): bool
    {
        return ! $this->column->isNull();
    }

    public function isNull(): bool
    {
        return $this->column->isNull();
    }

    public function isUnique(): bool
    {
        return $this->column->isUnique();
    }

    public function dataType(): string
    {
        return $this->column->type();
    }

    public function phpDataType(): string
    {
        return $this->phpDataTypes[$this->dataType()] ?? $this->dataType();
    }

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
     */
    public function image(): string
    {
        return $this->column->isFile();
    }

    /**
     * Whether current column is uploadable file or not?
     */
    public function file(): string
    {
        return $this->column->isFile();
    }

    /**
     * For Enum data type there are some predefined data set.
     */
    public function options(): ?array
    {
        return $this->column->options();
    }

    public function isForeign(): bool
    {
        return $this->column->isForeign();
    }

    public function foreignKey(): ?\LaraCrud\Helpers\ForeignKey
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
        if (! $this->isNull()) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        if ($this->isUnique()) {
            $rules[] = sprintf("Rule::unique('%s','%s')", $this->table->name(), $this->name());
        }
        if ($this->isForeign()) {
            $rules[] = sprintf(
                "Rule::exists('%s','%s')",
                $this->column->foreignTable(),
                $this->column->foreignColumn()
            );
        }
        if ('enum' == $this->dataType()) {
            $rules[] = 'in:' . implode(',', $this->options());
        } elseif ($this->file()) {
            $rules[] = 'file';
        } elseif (in_array($this->dataType(), ['varchar'])) {
            $rules[] = 'max:' . $this->length();
        } elseif ('tinyint' == $this->dataType() && 1 == $this->length()) {
            $rules[] = 'boolean';
        } elseif (
            in_array(
                $this->dataType(),
                ['smallint', 'int', 'mediumint', 'bigint', 'decimal', 'float', 'double']
            )
        ) {
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
