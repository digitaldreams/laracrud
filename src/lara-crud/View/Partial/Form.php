<?php

namespace LaraCrud\View\Partial;

use DbReader\Column;
use DbReader\Table;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Page;

/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */
class Form extends Page
{


    /**
     * @var array
     */
    protected $viewRules = [];

    /**
     * Where this form will be used. Possible option are modal or page
     * @var
     */
    protected $modal = false;
    /**
     * @var array
     */
    public $inputType = [
        'text' => 'textarea',
        'bigtext' => 'textarea',
        'tinytext' => 'textarea',
        'mediumtext' => 'textarea',
        'enum' => 'select',
        'int' => 'number',
        'bigint' => 'number',
        'varchar' => 'text',
        'timestamp' => 'datetime',
        'time' => 'time',
        'date' => 'date',
        'datetime' => 'datetime',
        'enum' => 'select',
        'tinyint' => 'checkbox',
    ];

    /**
     * Form constructor.
     * @param Table $table
     * @param string $name
     */
    public function __construct(Table $table, $name = '')
    {
        $this->table = $table;
        $this->folder = 'forms';
        $this->name = !empty($name) ? $name : str_singular($this->table->name());
        parent::__construct();
    }

    /**
     * Return fully completed form code as string
     * @return string
     */
    function template()
    {
        return (new TemplateManager("view/{$this->version}/forms/form.html", [
            'formContent' => implode("\n", $this->make()),
            'table' => $this->table->name(),
            'options' => $this->makeOptions(),
            'routeName' => $this->getRouteName('store', $this->table->name())
        ]))->get();
    }

    /**
     * Form Tag may have some options. E.g. enctype when uploading file
     */
    protected function makeOptions()
    {
        $retStr = '';
        $options = [];
        if ($this->table->hasFile()) {
            $options['enctype'] = 'multipart/form-data';
        }
        foreach ($options as $prop => $value) {
            $retStr .= $prop . '="' . $value . '" ';
        }
        return $retStr;
    }

    /**
     * Making form code
     * @return string
     */
    public function make()
    {
        $retArr = [];
        $columns = $this->table->columnClasses();

        foreach ($columns as $column) {
            if ($column->isIgnore() || $column->isProtected()) {
                continue;
            } elseif (in_array($column->type(), ['json', 'blob'])) {
                continue;
            }
            $columnArr = $this->processColumn($column);

            switch ($columnArr['type']) {
                case 'select':
                    $retArr[] = $this->select($columnArr, $column);
                    break;
                case 'checkbox':
                    $retArr[] = $this->checkBox($column);
                    break;
                case 'radio':
                    $retArr[] = $this->tempMan("radio.html", [], $column);
                    break;
                case 'file':
                    $retArr[] = $this->tempMan("file.html", [
                        'type' => 'file'
                    ], $column);
                    break;
                case 'date':
                case 'datetime':
                case 'time':
                    $propertiesText = '';
                    if (is_array($columnArr['properties'])) {
                        foreach ($columnArr['properties'] as $name => $value) {
                            $propertiesText .= $name . '="' . $value . '" ';
                        }
                    }
                    $retArr[] = $this->tempMan("date.html", [
                        'properties' => $propertiesText,
                        'type' => $columnArr['type'],
                        'columnValue' => '{{old(\'' . $column->name() . '\',$model->' . $column->name() . ')}}'
                    ], $column);
                    break;
                case 'textarea':
                    $retArr[] = $this->tempMan('textarea.html', [
                        'columnValue' => '{{old(\'' . $column->name() . '\',$model->' . $column->name() . ')}}'
                    ], $column);
                    break;
                default:
                    $propertiesText = '';
                    if (is_array($columnArr['properties'])) {
                        foreach ($columnArr['properties'] as $name => $value) {
                            $propertiesText .= $name . '="' . $value . '" ';
                        }
                    }
                    $retArr[] = $this->tempMan('default.html', [
                        'properties' => $propertiesText,
                        'columnValue' => '{{old(\'' . $column->name() . '\',$model->' . $column->name() . ')}}'

                    ], $column);
                    break;
            }
        }
        return $retArr;
    }

    /**
     * Generate select field
     * @param array $column
     * @param Column $columnObj
     * @return string
     */
    protected function select($column, Column $columnObj)
    {
        $options = '';
        if ($columnObj->isForeign()) {
            $options = $this->tempMan("select-rel.html", [
                'modelVar' => $columnObj->foreignTable(),
                'name' => $columnObj->name()
            ], $columnObj);
        } else {
            if (isset($column['options']) && is_array($column['options'])) {
                foreach ($column['options'] as $opt) {
                    $selectedText = '{{old(\'' . $column['name'] . '\',$model->' . $column['name'] . ')==\'' . $opt . '\'?"selected":""}}';
                    $label = ucwords(str_replace("_", " ", $opt));
                    $options .= '<option value="' . $opt . '" ' . $selectedText . ' >' . $label . '</option>' . "\n";
                }
            }
        }
        return $this->tempMan("select.html", ['options' => $options], $columnObj);
    }

    /**
     * Generate Checkbox
     * @param Column $column
     * @return string
     */
    protected function checkBox($column)
    {
        return $this->tempMan("checkbox.html", [
            'label' => $column->label(),
            'checked' => ''
        ], $column);
    }

    /**
     *
     * @param Column $column
     * [
     * type=> any valid input type e.g text,email,number,url,date,time,datetime,textarea,select
     * properties => if any property found e.g maxlength, max, min, required,placeholder
     * label=> Label of the input field
     * name=> name of the column
     * options=> for checkox, radio and select
     * @return array
     * ]
     */
    protected function processColumn(Column $column)
    {
        $options = [];
        $options['properties'] = [];

        if ($column->type() == 'enum') {
            $options['options'] = $column->options();
        } elseif ($column->type() == 'varchar') {
            $options['properties']['maxlength'] = (int)$column->length();
        }

        if (!$column->isNull()) {
            $options['properties']['required'] = 'required';
        }

        if ($column->isForeign()) {
            $options['type'] = 'select';
        } elseif ($column->isFile()) {
            $options['type'] = 'file';
        } else {
            $options['type'] = isset($this->inputType[$column->type()]) ? $this->inputType[$column->type()] : 'text';
        }
        $options['name'] = $column->name();
        return $options;
    }

    /**
     * For internal use only. For easy use of TempManager class
     * @param $fileName
     * @param array $options
     * @param Column $column
     * @return string
     */
    protected function tempMan($fileName, $options = [], Column $column)
    {
        $common = [
            'hasErrorClass' => $this->hasErr($column),
            'showErrorText' => $this->showErr($column),
            'name' => $column->name(),
            'label' => $column->label(),
            'type' => isset($this->inputType[$column->type()]) ? $this->inputType[$column->type()] : 'text'
        ];
        return (new TemplateManager("view/{$this->version}/forms/$fileName", array_merge($options, $common)))->get();
    }

    /**
     * @param $column
     * @return string
     */
    protected function hasErr(Column $column)
    {
        return (new TemplateManager("view/{$this->version}/hasErrorClass.txt", ['column' => $column->name()]))->get();
    }

    /**
     * @param $column
     * @return string
     */
    protected function showErr($column)
    {
        return (new TemplateManager("view/{$this->version}/showErrorText.txt", ['column' => $column->name()]))->get();
    }

}