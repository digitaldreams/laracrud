<?php

namespace LaraCrud\View;

/*
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

use DbReader\Table;
use LaraCrud\Helpers\TemplateManager;

class Blank extends Page
{
    protected $form;

    public function __construct(Table $table, $name = '')
    {
        $this->table = $table;
        $this->setFolderName();
        $this->name = !empty($name) ? $name : config('laracrud.view.page.create.name');
        parent::__construct();
    }

    /**
     * @return string
     */
    public function template()
    {
        return (new TemplateManager("view/{$this->version}/pages/blank.html", [
            'layout'     => config('laracrud.view.layout'),
            'table'      => $this->table->name(),
            'indexRoute' => static::getRouteName('index', $this->table->name()),
        ]))->get();
    }
}
