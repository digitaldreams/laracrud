<?php

namespace LaraCrud;

/**
 * Description of ViewCrud
 *
 * @author Tuhin
 */
class ViewCrud extends LaraCrud
{
    const TYPE_PANEL       = 'panel';
    const TYPE_TABLE       = 'table';
    const TYPE_TABLE_PANEL = 'tabpan';
    const PAGE_INDEX       = 'index';
    const PAGE_FORM        = 'form';
    const PAGE_DETAILS     = 'details';
    const PAGE_MODAL       = 'modal';

    /**
     * This is the third parameter of details page
     */
    const TYPE_RELATION = 'relation';

    protected $viewRules = [];
    public $inputType    = [
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
    /*     * *
     * Viwe path
     */
    public $path         = '';

    /**
     * Name of the model
     * @var type
     */
    public $modelName = '';

    /**
     * Page Type
     * @var string
     */
    public $type;

    /**
     * Page Name
     * @var string
     */
    public $page;

    /**
     * File Name
     */
    public $fileName = '';

    /**
     *
     * @var array
     */
    public $columns = [];

    /**
     * Foreign Columns
     * @var array
     */
    protected $foreginColumns = [];

    public function __construct($table = '', $page = '', $type = 'panel',
                                $name = '')
    {
        if (!empty($table)) {
            $this->mainTable = $table;
            if (is_array($table)) {
                $this->tables = $table;
            } else {
                $this->tables[] = $table;
            }
        } else {
            $this->getTableList();
        }
        $this->page     = $page;
        $this->type     = $type;
        $this->fileName = $name;

        $this->loadDetails();
        $this->prepareRelation();
        $this->makeRules();
    }

    /**
     * 
     */
    protected function makeRules()
    {
        foreach ($this->tableColumns as $tname => $tableColumns) {
            $this->checkForeignKeys($tname);
            foreach ($tableColumns as $column) {

                if (in_array($column->Field, $this->protectedColumns)) {
                    continue;
                }
                $this->columns[$tname][] = $column->Field;

                $this->viewRules[$tname][] = $this->processColumn($column,
                    $tname);
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
    protected function processColumn($column, $tableName)
    {
        $options               = [
        ];
        $type                  = $column->Type;
        $options['properties'] = [];

        if (strpos($type, "(")) {
            $type            = substr($column->Type, 0,
                strpos($column->Type, "("));
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

        if (isset($this->foreignKeys[$tableName]) && in_array($column->Field,
                $this->foreignKeys[$tableName]['keys'])) {
            $options['type'] = 'select';
        } else {
            $options['type'] = isset($this->inputType[$type]) ? $this->inputType[$type]
                    : 'text';
        }
        // $options['type'] = isset($this->inputType[$type]) ? $this->inputType[$type] : 'text';
        $options['name'] = $column->Field;
        return $options;
    }

    public function generateIndex($table = '')
    {
        $headerHtml = '';
        $tableName  = !empty($table) ? $table : $this->mainTable;
        $modelName  = strtolower($this->getModelName($tableName));

        $bodyHtml = '<?php foreach($records as $record): ?><tr>'."\n";

        foreach ($this->columns[$tableName] as $column) {
            $headerHtml.='<th>'.ucwords(str_replace("_", " ", $column)).'</th>'."\n";
            $bodyHtml.='<td>'."\n".'<?php echo $record->'.$column.'; ?>'."\n".'</td>'."\n";
        }
        $headerHtml.='<th>&nbsp;</th>'."\n";
        $headerHtml.='<th>&nbsp;</th>'."\n";

        $bodyHtml.='<td>'."\n".'<a href="<?php echo route(\''.$modelName.'.edit\',$record->id); ?>"><span class="glyphicon glyphicon-pencil"></span></a>'."\n".'</td>'."\n";
        $bodyHtml.='<td>'."\n".'<a onclick="return confirm(\'Are you sure you want to delete this record\')" href="<?php echo route(\''.$modelName.'.delete\',$record->id); ?>"><span class="glyphicon glyphicon-remove"></span></a>'."\n".'</td>'."\n";

        $bodyHtml.= '</tr><?php endforeach; ?>';
        $indexPageTemp = $this->getTempFile('view/index.html');
        $indexPageTemp = str_replace('@@tableHeader@@', $headerHtml,
            $indexPageTemp);
        $indexPageTemp = str_replace('@@tableBody@@', $bodyHtml, $indexPageTemp);
        $indexPageTemp = str_replace('@@table@@', $modelName, $indexPageTemp);
        return $indexPageTemp;
    }

    public function generateIndexPanel($table = '')
    {
        $retHtml = '<?php foreach($records as $record): ?>'."\n";

        $tableName = !empty($table) ? $table : $this->mainTable;
        $modelName = strtolower($this->getModelName($tableName));

        $retHtml.=$this->panelBox($tableName);
        $retHtml.='<?php endforeach; ?>';

        $retHtml.="\n\n\n";

        $modalHtml      = $this->generateModal($table);
        $panelmodalTemp = $this->getTempFile('view/index_panel_modal.html');
        $panelmodalTemp = str_replace("@@indexHtml@@", $retHtml, $panelmodalTemp);
        $panelmodalTemp = str_replace("@@modalHtml@@", $modalHtml,
            $panelmodalTemp);
        $panelmodalTemp = str_replace("@@table@@", $modelName, $panelmodalTemp);

        return $panelmodalTemp;
    }

    protected function panelBox($tableName)
    {
        $dataOption = '';
        $bodyHtml   = '';
        $modelName  = strtolower($this->getModelName($tableName));

        foreach ($this->columns[$tableName] as $column) {
            $dataOption.='data-'.$column.'="<?php echo $record->'.$column.';?>"'."\n";
            $bodyHtml.='<tr>'."\n".'<th>'.ucwords(str_replace("_", " ", $column))."\n".'</th>'."\n";
            $bodyHtml.='<td>'."\n".'<?php echo $record->'.$column.'; ?>'."\n".'</td></tr>'."\n";
        }
        $headline      = '<?php echo $record->id; ?>';
        $indexPageTemp = $this->getTempFile('view/index_panel.html');
        $indexPageTemp = str_replace(' @@headline@@', $headline, $indexPageTemp);
        $indexPageTemp = str_replace('@@modalName@@', $tableName.'Modal',
            $indexPageTemp);
        $indexPageTemp = str_replace('@@dataOptions@@', $dataOption,
            $indexPageTemp);
        $indexPageTemp = str_replace('@@tableBody@@', $bodyHtml, $indexPageTemp);
        $indexPageTemp = str_replace('@@table@@', $modelName, $indexPageTemp);
        return $indexPageTemp;
    }

    protected function tabPanel($tableName)
    {
        $dataOption = '';
        $bodyHtml   = '';


        $headerHtml = '';
        $tableName  = !empty($table) ? $table : $this->mainTable;
        $modelName  = strtolower($this->getModelName($tableName));
        $bodyHtml   = '<?php foreach($records as $record): ?><tr>'."\n";

        foreach ($this->columns[$tableName] as $column) {
            $dataOption.='data-'.$column.'="<?php echo $record->'.$column.';?>"'."\n";

            $headerHtml.='<th>'."\n".''.ucwords(str_replace("_", " ", $column)).''."\n".'</th>'."\n";
            $bodyHtml.='<td>'."\n".'<?php echo $record->'.$column.'; ?>'."\n".'</td>'."\n";
        }
        $headerHtml.='<th>&nbsp;</th>'."\n";
        $headerHtml.='<th>&nbsp;</th>'."\n";

        $bodyHtml.='<td>'."\n".'<a  data-toggle="modal" data-target="#'.$tableName.'Modal"'.$dataOption.' >'."\n".'<span class="glyphicon glyphicon-pencil"></span>'."\n".'</a>'."\n".'</td>'."\n";
        $bodyHtml.='<td>'."\n".'<a onclick="return confirm(\'Are you sure you want to delete this record\')" href="<?php echo route(\''.$tableName.'.delete\',$record->id); ?>">'."\n".'<span class="glyphicon glyphicon-remove"></span>'."\n".'</a>'."\n".'</td>'."\n";

        $bodyHtml.= '</tr><?php endforeach; ?>'."\n".'';

        $indexPageTemp = $this->getTempFile('view/panel_table.html');
        $indexPageTemp = str_replace('@@tableHeader@@', $headerHtml,
            $indexPageTemp);
        $indexPageTemp = str_replace('@@tableBody@@', $bodyHtml, $indexPageTemp);
        $indexPageTemp = str_replace('@@table@@', $modelName, $indexPageTemp);
        $modalHtml     = $this->generateModal($tableName);
        $indexPageTemp = str_replace('@@modalHtml@@', $modalHtml, $indexPageTemp);
        return $indexPageTemp;
    }

    protected function generateContent($table, $error_block = FALSE)
    {
        $retHtml = '';
        foreach ($this->viewRules[$table] as $column) {

            $templateContent = '';
            if ($column['type'] == 'select') {

                $templateContent = $this->getSelectContent($column, $error_block);
            } elseif ($column['type'] == 'checkbox') {
                $templateContent = $this->getCheckBoxContent($column,
                    $error_block);
            } elseif ($column['type'] == 'radio') {
                $templateContent = $this->getTempFile('view/radio.txt');
            } elseif ($column['type'] == 'textarea') {
                $templateContent = $this->getTempFile('view/textarea.txt');
            } else {
                $templateContent = $this->getTempFile('view/default.txt');
            }

            $templateContent = str_replace('@@name@@', $column['name'],
                $templateContent);
            $templateContent = str_replace('@@label@@',
                ucwords(str_replace("_", " ", $column['name'])),
                $templateContent);
            $templateContent = str_replace('@@type@@', $column['type'],
                $templateContent);

            $hasErrorBlockText = $this->hasErrorClass($column['name'],
                $error_block);
            $templateContent   = str_replace('@@hasErrorClass@@',
                $hasErrorBlockText, $templateContent);

            $showErrorText   = $this->showErrorText($column['name'],
                $error_block);
            $templateContent = str_replace('@@showErrorText@@', $showErrorText,
                $templateContent);
            $showColumnValue = '';

            if ($error_block) {
                $showColumnValue = '<?php echo old(\''.$column['name'].'\',$model->'.$column['name'].')?>';
            }
            $templateContent = str_replace('@@columnValue@@', $showColumnValue,
                $templateContent);

            $propertiesText = '';
            if (is_array($column['properties'])) {
                foreach ($column['properties'] as $name => $value) {
                    $propertiesText.=$name.'="'.$value.'" ';
                }
            }
            $templateContent = str_replace('@@properties@@', $propertiesText,
                $templateContent);
            $retHtml.=$templateContent;
//@@properties@@
        }
        return $retHtml;
    }

    protected function generateForm($table)
    {
        $modelName = strtolower($this->getModelName($table));

        $formContent  = $this->generateContent($table, TRUE);
        $formTemplate = $this->getTempFile('view/form.html');
        $formTemplate = str_replace('@@formContent@@', $formContent,
            $formTemplate);
        $formTemplate = str_replace('@@table@@', $modelName, $formTemplate);
        return $formTemplate;
    }

    public function generateModal($table)
    {
        $modalInputFill   = '';
        $modalInputClean  = '';
        $modalShowOnError = '';
        foreach ($this->columns[$table] as $column) {
            $modalShowOnError.=' $errors->has("'.$column.'") ||';
            $modalInputFill.='jq("#'.$column.'").val(btn.attr(\'data-'.$column.'\'));'."\n";
            $modalInputClean.='jq("#'.$column.'").val(\'\');'."\n";
        }
        $modalShowOnError = rtrim($modalShowOnError, "||");
        $modalInputFill.='jq("#id").val(btn.attr(\'data-id\'));'."\n";
        $modalInputClean.='jq("#id").val(\'\');'."\n";

        $formHtml  = $this->generateContent($table);
        $modalTemp = $this->getTempFile('view/modal.html');
        $modalTemp = str_replace('@@modalName@@', $table.'Modal', $modalTemp);
        $modalTemp = str_replace('@@form@@', $formHtml, $modalTemp);
        $modalTemp = str_replace('@@table@@', $table, $modalTemp);

        $modalTemp = str_replace('@@showModalOnError@@', $modalShowOnError,
            $modalTemp);
        $modalTemp = str_replace('@@modalInputFillUp@@', $modalInputFill,
            $modalTemp);
        $modalTemp = str_replace('@@modalInputCleanUp@@', $modalInputClean,
            $modalTemp);

        return $modalTemp;
    }

    protected function prepareMake($table)
    {
        $pathToSave = $this->getViewPath($table);
        if (!file_exists($pathToSave)) {
            mkdir($pathToSave);
        }
        if ($this->page == static::PAGE_INDEX) {
            if ($this->type == static::TYPE_PANEL) {

                $idnexPanelContent = $this->generateIndexPanel($table);
                $this->saveFile($pathToSave.'/'.$this->getFileName('index').'.blade.php',
                    $idnexPanelContent);
            } elseif ($this->type == static::TYPE_TABLE_PANEL) {

                $idnexPanelContent = $this->tabPanel($table);
                $this->saveFile($pathToSave.'/'.$this->getFileName('index').'.blade.php',
                    $idnexPanelContent);
            } else {

                $idnexTableContent = $this->generateIndex($table);
                $this->saveFile($pathToSave.'/'.$this->getFileName('index').'.blade.php',
                    $idnexTableContent);
            }
        } elseif ($this->page == static::PAGE_FORM) {
            $formContent = $this->generateForm($table);

            $this->saveFile($pathToSave.'/'.$this->getFileName('form').'.blade.php',
                $formContent);
        } elseif ($this->page == static::PAGE_DETAILS) {

            $detailsHtml = $this->generateDetails($table);
            $this->saveFile($pathToSave.'/'.$this->getFileName('details').'.blade.php', $detailsHtml);
        } elseif ($this->page == static::PAGE_MODAL) {

            $modalHtml = $this->generateModal($table);
            $this->saveFile($pathToSave.'/_modal.blade.php', $modalHtml);
        } else {
            if ($this->type == static::TYPE_PANEL) {

                $idnexPanelContent = $this->generateIndexPanel($table);
                $this->saveFile($pathToSave.'/'.$this->getFileName('index').'.blade.php',
                    $idnexPanelContent);
            } else {

                $idnexTableContent = $this->generateIndex($table);
                $this->saveFile($pathToSave.'/'.$this->getFileName('index').'.blade.php',
                    $idnexTableContent);
            }
            $formContent = $this->generateForm($table);
            $this->saveFile($pathToSave.'/'.$this->getFileName('form').'.blade.php', $formContent);

            $detailsHtml = $this->generateDetails($table);
            $this->saveFile($pathToSave.'/'.$this->getFileName('details').'.blade.php', $detailsHtml);
        }
    }

    public function make()
    {
        $retHtml = '';

        if (!empty($this->mainTable) && !is_array($this->mainTable)) {
            $this->prepareMake($this->mainTable);
        } else {
            foreach ($this->tables as $table) {
                $this->prepareMake($table);
                //  $retHtml.=$this->generateContent($table);
            }
        }

        return $retHtml;
    }

    private function getViewPath($table)
    {
        return base_path('resources/views/'.strtolower($this->getModelName($table)));
    }

    public function hasErrorClass($column, $required)
    {
        $content = '';
        $temp    = $this->getTempFile('view/hasErrorClass.txt');
        $content = str_replace('@@column@@', $column, $temp);
        return $content;
    }

    public function showErrorText($column, $required)
    {
        $content = '';
        $temp    = $this->getTempFile('view/showErrorText.txt');
        $content = str_replace('@@column@@', $column, $temp);
        return $content;
    }

    public function getSelectContent($column, $required)
    {
        $templateContent = $this->getTempFile('view/select.txt');
        $options         = '';
        if (in_array($column['name'], array_keys($this->foreginColumns))) {
            $selectOptions = $this->getTempFile('view/select-rel.txt');
            $selectOptions = str_replace('@@modelVar@@',
                strtolower($this->foreginColumns[$column['name']]),
                $selectOptions);
            $options       = str_replace('@@name@@', $column['name'],
                $selectOptions);
        } else {
            if (isset($column['options']) && is_array($column['options'])) {
                foreach ($column['options'] as $opt) {
                    $selectedText = $required == true ? '<?php echo old(\''.$column['name'].'\',$model->'.$column['name'].')==\''.$opt.'\'?"selected":"" ?>'
                            : '';
                    $label        = ucwords(str_replace("_", " ", $opt));
                    $options.='<option value="'.$opt.'" '.$selectedText.' >'.$label.'</option>'."\n";
                }
            }
        }

        $templateContent = str_replace('@@options@@', $options, $templateContent);
        return $templateContent;
    }

    public function getCheckBoxContent($column, $required)
    {
        $templateContent = $this->getTempFile('view/checkbox.txt');
        $selectTmp       = '';
        if ($required) {
            $selectTmp = '<?php echo old("'.$column['name'].'",$model->'.$column['name'].')==""?"checked":"" ?>';
        }
        return str_replace('@@checked@@', $selectTmp, $templateContent);
    }

    public function generateDetails($table)
    {
        $temp      = $this->getTempFile('view/details.html');
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
        $modelName = strtolower($this->getModelName($table));
        $temp      = str_replace('@@panelHtmlBox@@', $modelHtml, $temp);
        $temp      = str_replace('@@relationshipData@@', $relationHtml, $temp);
        $temp      = str_replace('@@modalHtmlBox@@', $modalHtml, $temp);
        $temp      = str_replace('@@table@@', $modelName, $temp);
        return $temp;
    }

    protected function checkForeignKeys($table)
    {

        if (isset($this->finalRelationShips[$table])) {
            foreach ($this->finalRelationShips[$table] as $rel) {

                if ($rel['name'] == static::RELATION_BELONGS_TO) {
                    $this->foreginColumns[$rel['foreign_key']] = $rel['model'];
                }
            }
        }
    }

    public function getFileName($page)
    {
        if (!empty($this->fileName)) {
            return str_replace(".blade.php", "", $this->fileName);
        }
        return $page;
    }
}