<?php

namespace LaraCrud\View;

/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

use DbReader\Table;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Partial\Form;
use LaraCrud\View\Partial\Link;
use Illuminate\Support\Str;

class Edit extends Page
{
    /**
     * @var Form
     */
    protected $form;

    /**
     * Edit constructor.
     * @param Table $table
     * @param string $name
     */
    public function __construct(Table $table, $name = '')
    {
        $this->table = $table;
        $this->setFolderName();
        $this->name = !empty($name) ? $name : config('laracrud.view.page.edit.name');
        $this->form = new Form($this->table);

        parent::__construct();
    }

    /**
     * @return string
     */
    function template()
    {
        $prefix = config('laracrud.view.namespace') ? config('laracrud.view.namespace') . '::' : '';
        $link = new Link($this->table->name());
        return (new TemplateManager("view/{$this->version}/pages/edit.html", [
            'layout' => config('laracrud.view.layout'),
            'table' => $this->table->name(),
            'folder' => $prefix . $this->form->getFolder(),
            'routeModelKey' => $this->dataStore['routeModelKey'] ?? 'id',
            'partialFilename' => Str::singular($this->table->name()),
            'indexRoute' => $this->getRouteName('index', $this->table->name()),
            'createLink' => $link->create(),
            'showRoute' => $this->getRouteName('show', $this->table->name()),
            'updateRoute' => $this->getRouteName('update', $this->table->name())
        ]))->get();
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        if (!$this->form->isExists()) {
            $this->form->save();
        }
        parent::save();
    }
}