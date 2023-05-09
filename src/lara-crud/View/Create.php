<?php

namespace LaraCrud\View;

/*
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

use DbReader\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Partial\Form;

class Create extends Page
{
    protected $form;

    /**
     * Create constructor.
     *
     * @param Model  $model
     * @param string $name
     */
    public function __construct(Model $model, $name = '')
    {
        $this->model = $model;
        $this->table = new Table($this->model->getTable());
        $this->setFolderName();
        $this->name = !empty($name) ? $name : config('laracrud.view.page.create.name');
        $this->form = new Form($this->model);
        parent::__construct();
    }

    /**
     * @return string
     */
    public function template()
    {
        $prefix = config('laracrud.view.namespace') ? config('laracrud.view.namespace') . '::' : '';

        return (new TemplateManager("view/{$this->version}/pages/create.html", [
            'layout'          => config('laracrud.view.layout'),
            'table'           => $this->table->name(),
            'folder'          => $prefix . $this->form->getFolder(),
            'routeModelKey'   => $this->model->getRouteKeyName(),
            'partialFilename' => Str::singular($this->table->name()),
            'indexRoute'      => static::getRouteName('index', $this->table->name()),
        ]))->get();
    }

    /**
     * @throws \Exception
     *
     * @return mixed|void
     */
    public function save()
    {
        if (!$this->form->isExists()) {
            $this->form->save();
        }
        parent::save();
    }
}
