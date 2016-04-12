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

    const TYPE_PANEL = 'panel';
    const TYPE_TABLE = 'table';
    const PAGE_INDEX = 'index';
    const PAGE_FORM = 'form';
    const PAGE_DETAILS = 'details';

    /**
     * This is the third parameter of details page
     */
    const TYPE_RELATION = 'relation';

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
    public $page;
    public $columns = [];
    protected $foreginColumns = [];

    public function __construct($table = '', $page = '', $type = 'panel') {
        if (!empty($table)) {
            $this->mainTable = $table;
            $this->tables[] = $table;
        } else {
            $this->getTableList();
        }
        $this->page = $page;
        $this->type = $type;


        $this->loadDetails();
        $this->prepareRelation();
        $this->makeRules();
    }

    /**
     * 
     */
    protected function makeRules() {
        foreach ($this->tableColumns as $tname => $tableColumns) {
            $this->checkForeignKeys($tname);
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
        $headerHtml.='<th>&nbsp;</th>' . "\n";
        $headerHtml.='<th>&nbsp;</th>' . "\n";

        $bodyHtml.='<td><a href="<?php echo route(\'' . $table . '.edit\',$record->id); ?>"><span class="glyphicon glyphicon-pencil"></span></a></td>' . "\n";
        $bodyHtml.='<td><a onclick="return confirm(\'Are you sure you want to delete this record\')" href="<?php echo route(\'' . $table . '.delete\',$record->id); ?>"><span class="glyphicon glyphicon-remove"></span></a></td>' . "\n";

        $bodyHtml.= '</tr><?php endforeach; ?>';
        $indexPageTemp = $this->getTempFile('view/index.html');
        $indexPageTemp = str_replace('@@tableHeader@@', $headerHtml, $indexPageTemp);
        $indexPageTemp = str_replace('@@tableBody@@', $bodyHtml, $indexPageTemp);
        $indexPageTemp = str_replace('@@table@@', $table, $indexPageTemp);
        return $indexPageTemp;
    }

    public function generateIndexPanel($table = '') {
        $retHtml = '<?php foreach($records as $record): ?>' . "\n";

        $tableName = !empty($table) ? $table : $this->mainTable;

        $retHtml.=$this->panelBox($tableName);
        $retHtml.='<?php endforeach; ?>';

        $retHtml.="\n\n\n";

        $modalHtml = $this->generateModal($table);
        $panelmodalTemp = $this->getTempFile('view/index_panel_modal.html');
        $panelmodalTemp = str_replace("@@indexHtml@@", $retHtml, $panelmodalTemp);
        $panelmodalTemp = str_replace("@@modalHtml@@", $modalHtml, $panelmodalTemp);
        $panelmodalTemp = str_replace("@@table@@", $table, $panelmodalTemp);

        return $panelmodalTemp;
    }

    protected function panelBox($tableName) {
        $dataOption = '';
        $bodyHtml = '';
        foreach ($this->columns[$tableName] as $column) {
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
        $indexPageTemp = str_replace('@@table@@', $tableName, $indexPageTemp);
        return $indexPageTemp;
    }

    protected function generateContent($table, $error_block = FALSE) {
        $retHtml = '';
        foreach ($this->viewRules[$table] as $column) {

            $templateContent = '';
            if ($column['type'] == 'select') {
                $templateContent = $this->getTempFile('view/select.txt');
                $options = '';
                if (in_array($column['name'], array_keys($this->foreginColumns))) {
                    $selectOptions = $this->getTempFile('view/select-rel.txt');
                    $selectOptions = str_replace('@@modelVar@@', strtolower($this->foreginColumns[$column['name']]), $selectOptions);
                    $options = str_replace('@@name@@', $column['name'], $selectOptions);
                } else {
                    if (isset($column['options']) && is_array($column['options'])) {
                        foreach ($column['options'] as $opt) {
                            $label = ucwords(str_replace("_", " ", $opt));
                            $options.='<option value="' . $opt . '">' . $label . '</option>';
                        }
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

            $hasErrorBlockText = $this->hasErrorClass($column['name'], $error_block);
            $templateContent = str_replace('@@hasErrorClass@@', $hasErrorBlockText, $templateContent);

            $showErrorText = $this->showErrorText($column['name'], $error_block);
            $templateContent = str_replace('@@showErrorText@@', $showErrorText, $templateContent);
            $showColumnValue = '';

            if ($error_block) {
                $showColumnValue = '<?php echo $model->' . $column['name'] . '?>';
            }
            $templateContent = str_replace('@@columnValue@@', $showColumnValue, $templateContent);

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

    protected function generateForm($table) {
        $formContent = $this->generateContent($table, TRUE);
        $formTemplate = $this->getTempFile('view/form.html');
        $formTemplate = str_replace('@@formContent@@', $formContent, $formTemplate);
        $formTemplate = str_replace('@@table@@', $table, $formTemplate);
        return $formTemplate;
    }

    public function generateModal($table) {
        $modalInputFill = '';
        $modalInputClean = '';

        foreach ($this->columns[$table] as $column) {
            $modalInputFill.='jq("#' . $column . '").val(btn.attr(\'data-' . $column . '\'));' . "\n";
            $modalInputClean.='jq("#' . $column . '").val(\'\');' . "\n";
        }
        $modalInputFill.='jq("#id").val(btn.attr(\'data-id\'));' . "\n";
        $modalInputClean.='jq("#id").val(\'\');' . "\n";

        $formHtml = $this->generateContent($table);
        $modalTemp = $this->getTempFile('view/modal.html');
        $modalTemp = str_replace('@@modalName@@', $table . 'Modal', $modalTemp);
        $modalTemp = str_replace('@@form@@', $formHtml, $modalTemp);
        $modalTemp = str_replace('@@table@@', $table, $modalTemp);

        $modalTemp = str_replace('@@modalInputFillUp@@', $modalInputFill, $modalTemp);
        $modalTemp = str_replace('@@modalInputCleanUp@@', $modalInputClean, $modalTemp);

        return $modalTemp;
    }

    protected function prepareMake($table) {
        $pathToSave = $this->getViewPath($table);
        if (!file_exists($pathToSave)) {
            mkdir($pathToSave);
        }
        if ($this->page == static::PAGE_INDEX) {
            if ($this->type == static::TYPE_PANEL) {
                $idnexPanelContent = $this->generateIndexPanel($table);
                $this->saveFile($pathToSave . '/index.blade.php', $idnexPanelContent);
            } else {
                $idnexTableContent = $this->generateIndex($table);
                $this->saveFile($pathToSave . '/index.blade.php', $idnexTableContent);
            }
        } elseif ($this->page == static::PAGE_FORM) {
            $formContent = $this->generateForm($table);

            $this->saveFile($pathToSave . '/form.blade.php', $formContent);
        } elseif ($this->page == static::PAGE_DETAILS) {
            $detailsHtml = $this->generateDetails($table);
            $this->saveFile($pathToSave . '/details.blade.php', $detailsHtml);
        } else {
            if ($this->type == static::TYPE_PANEL) {
                $idnexPanelContent = $this->generateIndexPanel($table);
                $this->saveFile($pathToSave . '/index.blade.php', $idnexPanelContent);
            } else {
                $idnexTableContent = $this->generateIndex($table);
                $this->saveFile($pathToSave . '/index.blade.php', $idnexTableContent);
            }
            $formContent = $this->generateForm($table);
            $this->saveFile($pathToSave . '/form.blade.php', $formContent);

            $detailsHtml = $this->generateDetails($table);
            $this->saveFile($pathToSave . '/details.blade.php', $detailsHtml);
        }
    }

    public function make() {
        $retHtml = '';

        if (!empty($this->mainTable)) {
            $this->prepareMake($this->mainTable);
        } else {
            foreach ($this->tables as $table) {
                $this->prepareMake($table);
                //  $retHtml.=$this->generateContent($table);
            }
        }

        return $retHtml;
    }

    private function getViewPath($table) {
        return base_path('resources/views/' . $table);
    }

    public function hasErrorClass($column, $required) {
        $content = '';
        if ($required) {
            $temp = $this->getTempFile('view/hasErrorClass.txt');
            $content = str_replace('@@column@@', $column, $temp);
        }
        return $content;
    }

    public function showErrorText($column, $required) {
        $content = '';
        if ($required) {
            $temp = $this->getTempFile('view/showErrorText.txt');
            $content = str_replace('@@column@@', $column, $temp);
        }
        return $content;
    }

    public function generateDetails($table) {
        $temp = $this->getTempFile('view/details.html');
        $modelHtml = $this->panelBox($table);

        $relationHtml = '';

        if ($this->type == static::TYPE_RELATION) {

            if (isset($this->finalRelationShips[$table])) {
                foreach ($this->finalRelationShips[$table] as $rel) {
                    if (in_array($rel['model'], ['belongsToMany', 'hasMany'])) {
                        $tableName = lcfirst(snake_case($rel['model']));

                        if (isset($this->tableColumns[$tableName])) {
                            
                        }
                    }
                }
            }
        }
        $modalHtml = $this->generateModal($table);
        $temp = str_replace('@@panelHtmlBox@@', $modelHtml, $temp);
        $temp = str_replace('@@relationshipData@@', $relationHtml, $temp);
        $temp = str_replace('@@modalHtmlBox@@', $modalHtml, $temp);
        $temp = str_replace('@@table@@', $table, $temp);
        return $temp;
    }

    protected function checkForeignKeys($table) {

        if (isset($this->finalRelationShips[$table])) {
            foreach ($this->finalRelationShips[$table] as $rel) {

                if ($rel['name'] == static::RELATION_BELONGS_TO) {
                    $this->foreginColumns[$rel['foreign_key']] = $rel['model'];
                }
            }
        }
    }

}
