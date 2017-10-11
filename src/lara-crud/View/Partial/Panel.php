<?php
/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

namespace LaraCrud\View\Partial;


use DbReader\Table;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Page;

class Panel extends Page
{
    /**
     * @var integer
     */
    protected $version;

    /**
     * @var string
     */
    protected $editedBy;

    /**
     * Panel constructor.
     * @param Table $table
     * @param string $name
     * @param string $editedBy
     */
    public function __construct(Table $table, $name = '', $editedBy = '')
    {

        $this->table = $table;
        $this->folder = $this->version == 3 ? 'panels' : 'cards';
        $this->name = !empty($name) ? $name : str_singular($this->table->name());
        $this->editedBy = !empty($editedBy) ? $editedBy : 'form';
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function template()
    {
        $bodyHtml = '';
        $columns = $this->table->columnClasses();
        foreach ($columns as $column) {
            if ($column->isIgnore() || $column->isProtected()) {
                continue;
            }
            $bodyHtml .= "\t\t".'<tr>' . PHP_EOL . "\t\t\t".'<th>' . $column->label() . '</th>' . PHP_EOL;
            $bodyHtml .= "\t\t\t".'<td>{{$record->' . $column->name() . '}}</td>'.PHP_EOL.
            "\t\t".'</tr>' . PHP_EOL;
        }
        $link = new Link($this->table->name());
        $tempMan = new TemplateManager("view/{$this->version}/panel.html", [
            'headline' => '{{$record->id}}',
            'table' => $this->table->name(),
            'showLink' => $link->show(),
            'editLink' => $this->editedBy == 'form' ? $link->edit() : $link->editModal($this->table),
            'deleteLink' => $link->destroy(),
            'tableBody' => $bodyHtml
        ]);
        return $tempMan->get();
    }

}