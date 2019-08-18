<?php

namespace LaraCrud\View\Partial;

use DbReader\Table;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Page;
use Illuminate\Support\Str;

/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */
class Modal extends Page
{
    /**
     * Modal constructor.
     * @param Table $table
     * @param string $name
     */
    public function __construct(Table $table, $name = '')
    {
        $this->table = $table;
        $this->folder = 'modals';
        $this->name = !empty($name) ? $name : Str::singular($this->table->name());
        parent::__construct();
    }

    /**
     * @return string
     */
    function template()
    {
        $modalInputFill = '';
        $modalInputClean = '';
        $modalShowOnError = '';
        $columns = $this->table->columnClasses();

        foreach ($columns as $column) {
            $modalShowOnError .= ' $errors->has("' . $column->name() . '") ||';

            if (!$column->isProtected() || $column->name() == 'id') {
                $modalInputFill .= 'jq("#' . $column->name() . '").val(btn.attr(\'data-' . $column->name() . '\'));' . PHP_EOL;
                $modalInputClean .= 'jq("#' . $column->name() . '").val(\'\');' . PHP_EOL;
            }

        }
        $modalShowOnError = rtrim($modalShowOnError, "||");
        return (new TemplateManager('view/modal.html', [
            'modalName' => $this->table->name() . "Modal",
            'form' => implode("\n", (new Form($this->table))->make()),
            'table' => $this->table->name(),
            'showModalOnError' => $modalShowOnError,
            'modalInputFillUp' => $modalInputFill,
            'modalInputCleanUp' => $modalInputClean
        ]))->get();
    }

}