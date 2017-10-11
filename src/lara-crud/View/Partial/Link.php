<?php
/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

namespace LaraCrud\View\Partial;


use DbReader\Table;
use LaraCrud\Helpers\TemplateManager;

class Link
{
    protected $table;

    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * Create link for Resourceful Route
     * @return string
     */
    public function create()
    {
        $version = config('laracrud.view.bootstrap');
        $temMan = new TemplateManager("view/$version/link/create.html", ['table' => $this->table]);
        return $temMan->get();
    }

    /**
     * Edit link for Resourceful Route
     * @return string
     */
    public function edit()
    {
        $version = config('laracrud.view.bootstrap');
        $temMan = new TemplateManager("view/$version/link/edit.html", ['table' => $this->table]);
        return $temMan->get();
    }


    /**
     * Edit link for Modal
     * @return string
     */
    public function editModal($table = '')
    {
        $dataOption = '';
        $table = is_object($table) ? $table : new Table($this->table);
        $columns = $table->columnClasses();
        foreach ($columns as $column) {
            if ($column->isIgnore() || $column->isProtected()) {
                continue;
            }
            $dataOption .= 'data-' . $column->name() . '="{{$record->' . $column->name() . '}}"' . PHP_EOL;

            $version = config('laracrud.view.bootstrap');
            $temMan = new TemplateManager("view/$version/link/edit_modal.html", [
                'modalName' => $this->table . "Modal",
                'dataOptions' => $dataOption
            ]);
            return $temMan->get();
        }
    }

    /**
     * @return string
     */
    public function show()
    {
        $version = config('laracrud.view.bootstrap');
        $temMan = new TemplateManager("view/$version/link/show.html", ['table' => $this->table]);
        return $temMan->get();
    }

    /**
     * Delete from for Resourceful Route
     * @return string
     */
    public function destroy()
    {
        $version = config('laracrud.view.bootstrap');
        $temMan = new TemplateManager("view/$version/link/destroy.html");
        return $temMan->get();
    }
}