<?php

namespace LaraCrud\View\Partial;

use DbReader\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Page;

/**
 * Tuhin Bepari <digitaldreams40@gmail.com>.
 */
class Modal extends Page
{
    /**
     * Modal constructor.
     *
     * @param Model  $model
     * @param string $name
     */
    public function __construct(Model $model, $name = '')
    {
        $this->model = $model;
        $this->table = new Table($model->getTable());
        $this->folder = 'modals';
        $this->name = !empty($name) ? $name : Str::singular($this->table->name());
        parent::__construct();
    }

    /**
     * @return string
     */
    public function template()
    {
        $modalInputFill = '';
        $modalInputClean = '';
        $modalShowOnError = '';
        $columns = $this->table->columnClasses();

        foreach ($columns as $column) {
            if ($this->isIgnoreAble($column)) {
                continue;
            }
            $modalShowOnError .= ' $errors->has("' . $column->name() . '") ||';
            $modalInputFill .= 'jq("#' . $column->name() . '").val(btn.attr(\'data-' . $column->name() . '\'));' . PHP_EOL;
            $modalInputClean .= 'jq("#' . $column->name() . '").val(\'\');' . PHP_EOL;
        }
        $modalShowOnError = rtrim($modalShowOnError, '||');

        return (new TemplateManager('view/modal.html', ['modalName' => $this->table->name() . 'Modal',
            'form' => implode("\n", (new Form($this->model))->make()),
            'table' => $this->table->name(),
            'showModalOnError' => $modalShowOnError,
            'modalInputFillUp' => $modalInputFill,
            'modalInputCleanUp' => $modalInputClean, ]))->get();
    }
}
