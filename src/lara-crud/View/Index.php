<?php
namespace LaraCrud\View;

use DbReader\Table;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Partial\Panel;
use LaraCrud\View\Partial\Table as TableView;

/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */
class Index extends Page
{
    public function __construct(Table $table, $name = '', $type = '')
    {

        $this->table = $table;
        $this->setFolderName();
        $this->name = !empty($name) ? $name : config('laracrud.view.page.index.name');
        $this->type = !empty($type) ? $type : config('laracrud.view.page.index.type');
        parent::__construct();
    }

    /**
     *
     */
    function template()
    {
        $file = '';
        $data = [
            'table' => $this->table->name(),
            'layout' => config('laracrud.view.layout'),
            'folder' => $this->version == 3 ? 'panels' : 'cards',
            'searchBox' => ''
        ];
        switch ($this->type) {
            case 'panel':
                $file = "view/{$this->version}/pages/index_panel.html";
                break;
            case 'table':
                $file = "view/{$this->version}/pages/index.html";
                break;
            default:
                $file = "view/{$this->version}/pages/index.html";
                break;
        }
        $tempMan = new TemplateManager($file, $data);
        return $tempMan->get();
    }

    /**
     *
     * @return string
     */
    protected function table()
    {
        $table = new TableView($this->table);
        return $table->save();
    }

    /**
     *
     */
    protected function panel()
    {
        $panel = new Panel($this->table);
        return $panel->save();
    }

    /**
     *
     */
    protected function searchBox()
    {
        return '';
    }

}