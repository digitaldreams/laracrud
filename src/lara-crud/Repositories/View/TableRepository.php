<?php

namespace LaraCrud\Repositories\View;

use LaraCrud\Contracts\View\TableContract;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\View\Partial\Link;

class TableRepository extends PageRepository implements TableContract
{
    /**
     * List of associative array each contains .
     * [
     *  order
     *  title
     *  property
     *  tag
     *  class
     *  template
     * ].
     *
     * @var array
     */
    protected array $columns = [];

    /**
     * Relative path of the file. E.g. pages.posts.index .
     *
     * @return string
     *
     * @throws \ReflectionException
     */
    public function path(): string
    {
        return 'tables.' . $this->getModelShortName();
    }

    /**
     * @return string
     */
    public function template(): string
    {
        return new TemplateManager('views/4/table.html', [
            'tableHeader' => $this->generateTableHeader(),
        ]);
    }

    /**
     * @param array $columns
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     *
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return array
     */
    public function makeColumns(): array
    {
        $data = [];
        foreach ($this->table->columns() as $columnRepository) {
            if ($columnRepository->isForeign()) {
                $data[] = $this->generateBelongsToLink();
            }

            if (in_array($columnRepository->dataType(), ['enum'])) {
                $data[] = $this->generateBadgeTag();
            }
        }
    }

    /**
     * Making Html code for Table Header and Body.
     *
     * @return array
     */
    public function make()
    {
        $headerhtml = '';
        $bodyhtml = '<tr>';
        $columns = $this->table->columnClasses();
        foreach ($columns as $column) {
            if ($this->isIgnoreAble($column)) {
                continue;
            } elseif (in_array($column->type(), ['text', 'longtext', 'mediumtext', 'tinytext', 'json', 'blob'])) {
                continue;
            }
            $headerhtml .= "\t\t<th>{$column->label()} </th>" . PHP_EOL;
            $bodyhtml .= "\t \t<td> {{" . '$record->' . "{$column->name()} }} </td>" . PHP_EOL;
        }
        $headerhtml .= "\t\t<th>&nbsp;</th>";
        $link = new Link($this->table->name());
        $routeKey = $this->model->getRouteKeyName();
        $bodyhtml .= "\t<td>" . $link->show($routeKey) . $link->edit($routeKey) . PHP_EOL . $link->destroy($routeKey) . '</td></tr>' . PHP_EOL;
        $bodyhtml = str_replace('@@table@@', $this->table->name(), $bodyhtml);

        return [
            'table' => $this->table->name(),
            'tableHeader' => $headerhtml,
            'routeModelKey' => $routeKey,
            'tableBody' => $bodyhtml,
        ];
    }

    /**
     * @return array
     */
    private function generateBelongsToLink(): array
    {
        return [];
    }

    /**
     * @return array
     */
    private function generateBadgeTag(): array
    {
        return [];
    }
}
