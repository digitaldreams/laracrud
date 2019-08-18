<?php

namespace LaraCrud\View;

/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

use DbReader\Table;
use Illuminate\Database\Eloquent\Model;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Partial\Link;
use LaraCrud\View\Partial\Panel;
use Illuminate\Support\Str;

class Show extends Page
{
    /**
     * @var Panel
     */
    protected $panel;

    /**
     * Show constructor.
     * @param Model $model
     * @param string $name
     * @param string $type
     */
    public function __construct(Model $model, $name = '', $type = '')
    {
        $this->model = $model;
        $this->table = new Table($this->model->getTable());
        $this->setFolderName();
        $this->type = $type;
        $this->name = !empty($name) ? $name : config('laracrud.view.page.show.name');
        $this->panel = new Panel($this->model);
        parent::__construct();
    }

    /**
     * @return string
     */
    function template()
    {
        $link = new Link($this->table->name());
        $prefix = config('laracrud.view.namespace') ? config('laracrud.view.namespace') . '::' : '';
        $routeKey = $this->dataStore['routeModelKey'] ?? 'id';
        return (new TemplateManager("view/{$this->version}/pages/show.html", [
            'table' => $this->table->name(),
            'layout' => config('laracrud.view.layout'),
            'folder' => $prefix . $this->panel->getFolder(),
            'routeModelKey' => $this->model->getRouteKeyName(),
            'partialFilename' => Str::singular($this->table->name()),
            'indexRoute' => $this->getRouteName('index', $this->table->name()),
            'buttons' => PHP_EOL . $link->create(get_class($this->model)) . PHP_EOL . $link->edit($routeKey) . PHP_EOL . $link->destroy($routeKey) . PHP_EOL
        ]))->get();
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        if (!$this->panel->isExists()) {
            $this->panel->save();
        }
        parent::save();
    }
}