<?php

namespace App\Libs;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ViewCrud
 *
 * @author Tuhin
 */
class ViewCrud extends LaraCrud {

    protected $mainTable = '';
    protected $protectedColumns = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $viewRules = [];
    public $inputType = [
        'text' => 'textarea',
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
    public $path = '';
    public $modelName = '';
    public $type;
    public $columns = [];

    public function __construct($table = '') {
        if (!empty($table)) {
            $this->mainTable = $table;
        }
        $this->getTableList();


        $this->loadDetails();
        $this->prepareRelation();
        $this->makeRules();
    }

    /**
     * 
     */
    protected function makeRules() {
        foreach ($this->tableColumns as $tname => $tableColumns) {
            foreach ($tableColumns as $column) {

                if (in_array($column->Field, $this->protectedColumns)) {
                    continue;
                }
                $this->columns[$tname][] = $column->Field;

                $this->viewRules[$tname][] = $this->processColumn($column, $tname);
            }
        }
    }

    /**
     * 
     * @param type $column
     * [
     * type=> any valid input type e.g text,email,number,url,date,time,datetime,textarea,select
     * properties => if any property found e.g maxlength, max, min, required,placeholder
     * label=> Label of the input field
     * name=> name of the column
     * options=> for checkox, radio and select
     * 
     * ]
     */
    protected function processColumn($column, $tableName) {
        $options = [
        ];
        $type = $column->Type;
        $options['properties'] = [];

        if (strpos($type, "(")) {
            $type = substr($column->Type, 0, strpos($column->Type, "("));
            $possibleOptions = $this->extractRulesFromType($column->Type);
            if (stripos($possibleOptions, ",")) {
                $options['options'] = explode(",", $possibleOptions);
            } else {
                $options['properties']['maxlength'] = (int) $possibleOptions;
            }
        }

        if ($column->Null == 'NO') {
            $options['properties']['required'] = 'required';
        }

        if (isset($this->foreignKeys[$tableName]) && in_array($column->Field, $this->foreignKeys[$tableName]['keys'])) {
            $options['type'] = 'select';
        } else {
            $options['type'] = isset($this->inputType[$type]) ? $this->inputType[$type] : 'text';
        }
        // $options['type'] = isset($this->inputType[$type]) ? $this->inputType[$type] : 'text';
        $options['name'] = $column->Field;
        return $options;
    }

    public function generateIndex($table = '') {
        $headerHtml = '';
        $tableName = !empty($table) ? $table : $this->mainTable;
        $bodyHtml = '<?php foreach($records as $record): ?><tr>' . "\n";

        foreach ($this->columns[$tableName] as $column) {
            $headerHtml.='<th>' . ucwords(str_replace("_", " ", $column)) . '</th>' . "\n";
            $bodyHtml.='<td><?php echo $record->' . $column . '; ?></td>' . "\n";
        }

        $bodyHtml.= '</tr><?php endforeach; ?>';
        $indexPageTemp = $this->getTempFile('view/index.html');
        $indexPageTemp = str_replace('@@tableHeader@@', $headerHtml, $indexPageTemp);
        $indexPageTemp = str_replace('@@tableBody@@', $bodyHtml, $indexPageTemp);
        return $indexPageTemp;
    }

    public function generateIndexPanel($table = '') {
        $retHtml = '<?php foreach($records as $record): ?>' . "\n";
        $dataOption = '';
        $tableName = !empty($table) ? $table : $this->mainTable;

        $bodyHtml = '';
        $modalInputFill = '';
        $modalInputClean = '';

        foreach ($this->columns[$tableName] as $column) {
            $modalInputFill.='jq("#' . $column . '").val(btn.attr(\'data-' . $column . '\'));' . "\n";
            $modalInputClean.='jq("#' . $column . '").val(\'\');' . "\n";
            $dataOption.='data-' . $column . '="<?php echo $record->' . $column . ';?>"' . "\n";
            $bodyHtml.='<tr><th>' . ucwords(str_replace("_", " ", $column)) . '</th>' . "\n";
            $bodyHtml.='<td><?php echo $record->' . $column . '; ?></td></tr>' . "\n";
        }
        $headline = '<?php echo $record->id; ?>';
        $indexPageTemp = $this->getTempFile('view/index_panel.html');
        $indexPageTemp = str_replace(' @@headline@@', $headline, $indexPageTemp);
        $indexPageTemp = str_replace('@@modalName@@', $tableName . 'Modal', $indexPageTemp);
        $indexPageTemp = str_replace('@@dataOptions@@', $dataOption, $indexPageTemp);
        $indexPageTemp = str_replace('@@tableBody@@', $bodyHtml, $indexPageTemp);
        $retHtml.=$indexPageTemp;
        $retHtml.='<?php endforeach; ?>';



        $formHtml = $this->generateContent($tableName);
        $modalTemp = $indexPageTemp = $this->getTempFile('view/modal.html');
        $modalTemp = str_replace('@@modalName@@', $tableName . 'Modal', $modalTemp);
        $modalTemp = str_replace('@@form@@', $formHtml, $modalTemp);

        $modalTemp = str_replace('@@modalInputFillUp@@', $modalInputFill, $modalTemp);
        $modalTemp = str_replace('@@modalInputCleanUp@@', $modalInputClean, $modalTemp);
        $retHtml.="\n\n\n";
        $retHtml.=$modalTemp;
        return $retHtml;
    }

    protected function generateContent($table) {
        $retHtml = '';
        foreach ($this->viewRules[$table] as $column) {

            $templateContent = '';
            if ($column['type'] == 'select') {
                $templateContent = $this->getTempFile('view/select.txt');
                $options = '';
                if (isset($column['options']) && is_array($column['options'])) {
                    foreach ($column['options'] as $opt) {
                        $label = ucwords(str_replace("_", " ", $opt));
                        $options.='<option value="' . $opt . '">' . $label . '</option>';
                    }
                }
                $templateContent = str_replace('@@options@@', $options, $templateContent);
            } elseif ($column['type'] == 'checkbox') {
                $templateContent = $this->getTempFile('view/checkbox.txt');
            } elseif ($column['type'] == 'radio') {
                $templateContent = $this->getTempFile('view/radio.txt');
            } elseif ($column['type'] == 'textarea') {
                $templateContent = $this->getTempFile('view/textarea.txt');
            } else {
                $templateContent = $this->getTempFile('view/default.txt');
            }
            $templateContent = str_replace('@@name@@', $column['name'], $templateContent);
            $templateContent = str_replace('@@label@@', ucwords(str_replace("_", " ", $column['name'])), $templateContent);
            $templateContent = str_replace('@@type@@', $column['type'], $templateContent);

            $propertiesText = '';
            if (is_array($column['properties'])) {
                foreach ($column['properties'] as $name => $value) {
                    $propertiesText.=$name . '="' . $value . '" ';
                }
            }
            $templateContent = str_replace('@@properties@@', $propertiesText, $templateContent);
            $retHtml.=$templateContent;
//@@properties@@
        }
        return $retHtml;
    }

    protected function generateModal() {
        
    }

    public function make() {
        $retHtml = '';
        foreach ($this->tables as $table) {
            $retHtml.=$this->generateContent($table);
        }
        return $retHtml;
    }

}
