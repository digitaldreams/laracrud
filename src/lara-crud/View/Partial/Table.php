<?php
namespace LaraCrud\View\Partial;

use DbReader\Table as TableReader;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Page;

/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */
class Table extends Page
{

    /**
     * Table constructor.
     * @param TableReader $table
     * @param string $name
     */
    public function __construct(TableReader $table, $name = '')
    {
        $this->table = $table;
        $this->folder = 'tables';
        $this->name = !empty($name) ? $name : str_singular($this->table->name());
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
        $bodyhtml = '';
        $columns = $this->table->columnClasses();
        foreach ($columns as $column) {
            if ($column->isIgnore() || $column->isProtected()) {
                continue;
            }
            $headerhtml .= "\t\t<th>{$column->label()} </th>" . PHP_EOL;
            $bodyhtml .= "\t \t<td> {{" . '$record->' . "{$column->name()} }} </td>" . PHP_EOL;
        }
        $headerhtml .= "\t\t<th>&nbsp;</th>";
        $link = new Link($this->table->name());
        $bodyhtml .= "\t<td>" . $link->edit() . PHP_EOL . $link->show() . "}}</td>" . PHP_EOL;

        return [
            'tableHeader' => $headerhtml,
            'tableBody' => $bodyhtml
        ];
    }

}