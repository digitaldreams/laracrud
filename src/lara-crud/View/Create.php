<?php

namespace LaraCrud\View;

/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

use DbReader\Table;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Partial\Form;

class Create extends Page
{
    protected $form;

    public function __construct(Table $table, $name = '')
    {
        $this->table = $table;
        $this->setFolderName();
        $this->name = !empty($name) ? $name : config('laracrud.view.page.create.name');
        $this->form = new Form($this->table);
        parent::__construct();
    }

    /**
     * @return string
     */
    function template()
    {
        return (new TemplateManager("view/{$this->version}/pages/create.html", [
            'layout' => config('laracrud.view.layout'),
            'table' => $this->table->name(),
            'partialFilename' => str_singular($this->table->name())
        ]))->get();
    }

    /**
     * @return mixed|void
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