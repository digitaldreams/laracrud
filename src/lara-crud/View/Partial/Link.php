<?php

namespace LaraCrud\View\Partial;

use DbReader\Table;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Page;

class Link
{
    protected $table;

    /**
     * Link constructor.
     *
     * @param $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }

    /**
     * Create link for Resourceful Route
     * @param string $model
     * @return string
     */
    public function create($model = '')
    {
        $version = config('laracrud.view.bootstrap');
        if (class_exists(Page::$policy) && method_exists(Page::$policy, 'create')) {
            $temMan = new TemplateManager("view/$version/link/policy/create.html", [
                'table' => $this->table,
                'createRoute' => Page::getRouteName('create', $this->table),
                'modelName' => $model . '::class'
            ]);
        } else {
            $temMan = new TemplateManager("view/$version/link/create.html", [
                'table' => $this->table,
                'createRoute' => Page::getRouteName('create', $this->table)
            ]);
        }
        return $temMan->get();
    }

    /**
     * Edit link for Resourceful Route
     * @param string $routeModelKey
     * @return string
     */
    public function edit($routeModelKey = 'id')
    {
        $version = config('laracrud.view.bootstrap');
        if (class_exists(Page::$policy) && method_exists(Page::$policy, 'update')) {
            $temMan = new TemplateManager("view/$version/link/policy/edit.html", [
                'table' => $this->table,
                'routeModelKey' => $routeModelKey,
                'editRoute' => Page::getRouteName('edit', $this->table)
            ]);
        } else {
            $temMan = new TemplateManager("view/$version/link/edit.html", [
                'table' => $this->table,
                'routeModelKey' => $routeModelKey,
                'editRoute' => Page::getRouteName('edit', $this->table)
            ]);
        }
        return $temMan->get();
    }

    /**
     * Edit link for Modal
     * @param string $table
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
     * @param string $routeModelKey
     * @return string
     */
    public function show($routeModelKey = 'id')
    {
        $version = config('laracrud.view.bootstrap');
        if (class_exists(Page::$policy) && method_exists(Page::$policy, 'view')) {
            $temMan = new TemplateManager("view/$version/link/policy/show.html", [
                'table' => $this->table,
                'routeModelKey' => $routeModelKey,
                'showRoute' => Page::getRouteName('show', $this->table)
            ]);
        } else {
            $temMan = new TemplateManager("view/$version/link/show.html", [
                'table' => $this->table,
                'routeModelKey' => $routeModelKey,
                'showRoute' => Page::getRouteName('show', $this->table)
            ]);
        }
        return $temMan->get();
    }

    /**
     * Delete from for Resourceful Route
     * @param string $routeModelKey
     * @return string
     */
    public function destroy($routeModelKey = 'id')
    {
        $version = config('laracrud.view.bootstrap');
        if (class_exists(Page::$policy) && method_exists(Page::$policy, 'delete')) {
            $temMan = new TemplateManager("view/$version/link/policy/destroy.html", [
                'routeModelKey' => $routeModelKey,
                'destroyRoute' => Page::getRouteName('destroy', $this->table)
            ]);
        } else {
            $temMan = new TemplateManager("view/$version/link/destroy.html", [
                'routeModelKey' => $routeModelKey,
                'destroyRoute' => Page::getRouteName('destroy', $this->table)
            ]);
        }
        return $temMan->get();
    }
}
