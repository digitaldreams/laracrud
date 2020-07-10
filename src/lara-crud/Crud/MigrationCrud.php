<?php

namespace LaraCrud\Crud;

use DbReader\Table;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;

/**
 * Description of MigrationCrud.
 *
 * @author Tuhin
 */
class MigrationCrud implements Crud
{
    use Helper;
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var array
     */
    protected $fk = [];

    /**
     * @var array
     */
    protected $columnMap = [
        'varchar'    => 'string',
        'char'       => 'char',
        'text'       => 'text',
        'mediumtext' => 'mediumText',
        'longtext'   => 'longText',
        'int'        => 'integer',
        'tinyint'    => 'tinyInteger',
        'smallint'   => 'smallInteger',
        'mediumint'  => 'mediumInteger',
        'bigint'     => 'bigInteger',
        'float'      => 'float',
        'double'     => 'double',
        'decimal'    => 'decimal',
        'enum'       => 'enum',
        'date'       => 'date',
        'datetime'   => 'dateTime',
        'time'       => 'time',
        'timestamp'  => 'timestamp',
    ];

    /**
     * MigrationCrud constructor.
     *
     * @param $table
     */
    public function __construct($table)
    {
        $this->table = new Table($table);
    }

    /**
     * Process template and return complete code.
     *
     * @return mixed
     */
    public function template()
    {
        $retContent = '';
        $rules = $this->rules();
        foreach ($rules as $r) {
            $retContent .= "\t"."\t"."\t".'$table->'.$r['methodName'];
            $retContent .= !empty($r['mainParams']) ? '("'.$r['columnName'].'",'.$r['mainParams'].')' : '("'.$r['columnName'].'")';
            if (!empty($r['otherMethods'])) {
                foreach ($r['otherMethods'] as $om) {
                    $retContent .= '->'.$om['name'].'('.$om['params'].')';
                }
            }
            $retContent .= ';'.PHP_EOL;
        }
        if (!empty($this->fk)) {
            foreach ($this->fk as $column => $rel) {
                if (!empty($rel['references']) && !empty($rel['on'])) {
                    $fkTemp = new TemplateManager('migration/foreign.txt', [
                        'column'     => $column,
                        'references' => $rel['references'],
                        'on'         => $rel['on'],
                    ]);
                    $retContent .= $fkTemp->get().PHP_EOL;
                }
            }
        }

        $mtemp = new TemplateManager('migration/template.txt', [
            'className' => $this->generateClassName($this->table->name()),
            'table'     => $this->table->name(),
            'content'   => $retContent,
        ]);

        return $mtemp->get();
    }

    /**
     * Get code and save to disk.
     *
     * @return mixed
     */
    public function save()
    {
        $fullPath = config('laracrud.migrationPath', 'database/migrations/').$this->generateName($this->table->name()).'.php';
        $migrationFile = new \SplFileObject($fullPath, 'w+');
        $migrationFile->fwrite($this->template());
    }

    /**
     * @return array
     */
    private function rules()
    {
        $retArr = [];
        $columns = $this->table->columnClasses();
        foreach ($columns as $column) {
            $arr = [];
            $params = '';
            $otherMethods = [];

            $arr['columnName'] = $columnName = $column->name();
            $dataType = $column->type();

            if ($column->isPk()) {
                if ('int' == $dataType) {
                    $arr['methodName'] = 'increments';
                } elseif ('bigint' == $dataType) {
                    $arr['methodName'] = 'bigIncrements';
                }
            } else {
                $arr['methodName'] = isset($this->columnMap[$dataType]) ? $this->columnMap[$dataType] : '';
            }
            //for enum data type we will use in validator.
            if ('enum' == $dataType) {
                $retVals = implode("', '", $column->options());
                $params = '[\''.$retVals.'\']';
            } elseif ('varchar' == $dataType) {
                $params = $column->length();
            } elseif ('tinyint' == $dataType) {
                if (1 == $column->length()) {
                    $arr['methodName'] = 'boolean';
                }
            } elseif (in_array($dataType, ['smallint', 'int', 'mediumint', 'bigint', 'float',
                'double', ])) {
                if (!empty($column->length())) {
                    $params = false;
                }
            } elseif ('decimal' == $dataType) {
                $startBrace = stripos($column->Type, '(');
                $endBrace = stripos($column->Type, ')');
                $pm = substr($column->Type, $startBrace, ($endBrace - $startBrace));

                if (!empty($pm)) {
                    $params = str_replace(['(', ')'], '', $pm);
                }
            }

            $defaultValue = $column->defaultValue();
            if (!empty($defaultValue)) {
                $otherMethods[] = [
                    'name'   => 'default',
                    'params' => "'".$defaultValue."'",
                ];
            }
            if ($column->isUnique()) {
                $otherMethods[] = [
                    'name'   => 'unique',
                    'params' => '',
                ];
            }
            if ($column->isForeign()) {
                $otherMethods[] = [
                    'name'   => 'unsigned',
                    'params' => '',
                ];
                $this->fk[$column->name()] = [
                    'references' => $column->foreignColumn(),
                    'on'         => $column->foreignTable(),
                ];
            }
            if ($column->isNull()) {
                $otherMethods[] = [
                    'name'   => 'nullable',
                    'params' => '',
                ];
            }

            $arr['mainParams'] = $params;

            $arr['otherMethods'] = $otherMethods;

            $retArr[$columnName] = $arr;
        }

        return $retArr;
    }

    /**
     * Generate Migration file name.
     *
     * @param $table
     *
     * @return string
     */
    public function generateName($table)
    {
        return date('Y_m_d_His').'_create_'.$table.'_table';
    }

    /**
     * Generate Class Name.
     *
     * @param $table
     *
     * @throws \Exception
     *
     * @return string
     */
    public function generateClassName($table)
    {
        $class = 'create'.ucfirst(camel_case($table)).'Table';

        if (class_exists($class)) {
            throw new \Exception('Migration for table '.$table.' already exists');
        }

        return $class;
    }
}
