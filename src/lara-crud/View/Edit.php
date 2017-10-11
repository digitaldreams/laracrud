<?php
namespace LaraCrud\View;

/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */
use DbReader\Table;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Partial\Form;

class Edit extends Page
{

    public function __construct(Table $table, $name = '')
    {
        $this->table = $table;
        $this->setFolderName();
        $this->name = !empty($name) ? $name : config('laracrud.view.page.edit.name');
        parent::__construct();
    }

    /**
     * @return string
     */
    function template()
    {
        return (new TemplateManager("view/{$this->version}/pages/edit.html", [
            'layout' => config('laracrud.view.layout'),
            'table' => $this->table->name(),
        ]))->get();
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        $formPath = 'views/forms/' . str_singular($this->table->name()) . ".blade.php";
        if (!file_exists(resource_path($formPath))) {
            $form = new Form($this->table);
            $form->save();
        }
        parent::save();
    }
}