<?php

namespace LaraCrud\View;

/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */
use DbReader\Table;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Partial\Link;
use LaraCrud\View\Partial\Panel;

class Show extends Page
{
    /**
     * @var Panel
     */
    protected $panel;

    /**
     * Show constructor.
     * @param Table $table
     * @param string $name
     * @param string $type
     */
    public function __construct(Table $table, $name = '', $type = '')
    {
        $this->table = $table;
        $this->setFolderName();
        $this->type = $type;
        $this->name = !empty($name) ? $name : config('laracrud.view.page.show.name');
        $this->panel = new Panel($this->table);
        parent::__construct();
    }

    /**
     * @return string
     */
    function template()
    {
        $link = new Link($this->table->name());
        $prefix = config('laracrud.view.namespace') ? config('laracrud.view.namespace') . '::' : '';

        return (new TemplateManager("view/{$this->version}/pages/show.html", [
            'table' => $this->table->name(),
            'layout' => config('laracrud.view.layout'),
            'folder' => $prefix . $this->panel->getFolder(),
            'partialFilename' => str_singular($this->table->name()),
            'indexRoute' => $this->getRouteName('index', $this->table->name()),
            'buttons' => PHP_EOL . $link->create() . PHP_EOL . $link->edit() . PHP_EOL . $link->destroy() . PHP_EOL
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