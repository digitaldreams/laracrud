<?php
/**
 * Tuhin Bepari <digitaldreams40@gmail.com>.
 */

namespace LaraCrud\View\Partial;

use DbReader\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Page;

class Panel extends Page
{
    /**
     * @var int
     */
    protected $version;

    /**
     * @var string
     */
    protected $editedBy;

    /**
     * Panel constructor.
     *
     * @param Model  $model
     * @param string $name
     * @param string $editedBy
     */
    public function __construct(Model $model, $name = '', $editedBy = '')
    {
        $this->model = $model;
        $this->table = new Table($model->getTable());
        $this->folder = 3 == $this->version ? 'panels' : 'cards';
        $this->name = !empty($name) ? $name : Str::singular($this->table->name());
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
            if ($this->isIgnoreAble($column)) {
                continue;
            } elseif (in_array($column->type(), ['text', 'longtext', 'mediumtext', 'tinytext', 'json', 'blob'])) {
                continue;
            }
            $bodyHtml .= "\t\t" . '<tr>' . PHP_EOL . "\t\t\t" . '<th>' . $column->label() . '</th>' . PHP_EOL;
            $bodyHtml .= "\t\t\t" . '<td>{{$record->' . $column->name() . '}}</td>' . PHP_EOL .
                "\t\t" . '</tr>' . PHP_EOL;
        }
        $link = new Link($this->table->name());
        $routeKey = $this->model->getRouteKeyName();
        $tempMan = new TemplateManager("view/{$this->version}/panel.html", [
            'headline' => $this->getTitleColumn(),
            'table' => $this->table->name(),
            'routeModelKey' => $routeKey,
            'showLink' => $link->show($routeKey),
            'showRoute' => Page::getRouteName('show', $this->table->name()),
            'editLink' => 'form' == $this->editedBy ? $link->edit($routeKey) : $link->editModal($this->table),
            'deleteLink' => $link->destroy($routeKey),
            'tableBody' => $bodyHtml,
        ]);

        return $tempMan->get();
    }
}
