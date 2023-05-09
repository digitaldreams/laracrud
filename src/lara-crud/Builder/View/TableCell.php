<?php

namespace LaraCrud\Builder\View;

use LaraCrud\Contracts\ColumnContract;

class TableCell
{
    /**
     * @var string
     */
    protected string $html;

    /**
     * TableCell constructor.
     */
    public function __construct(protected ColumnContract $columnContract)
    {
    }

    public function getHtml(): string
    {
        return $this->html;
    }
}
