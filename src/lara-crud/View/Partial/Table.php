<?php

namespace LaraCrud\View\Partial;

use DbReader\Table as TableReader;
use Illuminate\Database\Eloquent\Model;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Page;
use Illuminate\Support\Str;

/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */
class Table extends Page
{

    /**
     * Table constructor.
     * @param Model $model
     * @param string $name
     */
    public function __construct(Model $model, $name = '')
    {
        $this->model = $model;
        $this->table = new TableReader($model->getTable());
        $this->folder = 'tables';
        $this->name = !empty($name) ? $name : Str::singular($this->table->name());
        parent::__construct();
    }

    /**
     * Return the full table code
     * @return string
     */
    public function template()
    {
        $temMan = new TemplateManager("view/table.html", $this->make());
        return $temMan->get();
    }

    /**
     * Making Html code for Table Header and Body
     * @return array
     */
    public function make()
    {
        $headerhtml = '';
        $bodyhtml = '<tr>';
        $columns = $this->table->columnClasses();
        foreach ($columns as $column) {
            if ($this->isIgnoreAble($column)) {
                continue;
            } elseif (in_array($column->type(), ['text', 'longtext', 'mediumtext', 'tinytext', 'json', 'blob'])) {
                continue;
            }
            $headerhtml .= "\t\t<th>{$column->label()} </th>" . PHP_EOL;
            $bodyhtml .= "\t \t<td> {{" . '$record->' . "{$column->name()} }} </td>" . PHP_EOL;
        }
        $headerhtml .= "\t\t<th>&nbsp;</th>";
        $link = new Link($this->table->name());
        $routeKey = $this->model->getRouteKeyName();
        $bodyhtml .= "\t<td>" . $link->show($routeKey) . $link->edit($routeKey) . PHP_EOL . $link->destroy($routeKey) . "</td></tr>" . PHP_EOL;
        $bodyhtml = str_replace('@@table@@', $this->table->name(), $bodyhtml);
        return [
            'table' => $this->table->name(),
            'tableHeader' => $headerhtml,
            'routeModelKey' => $routeKey,
            'tableBody' => $bodyhtml
        ];
    }


}