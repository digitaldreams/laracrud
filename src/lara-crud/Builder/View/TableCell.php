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
     * @var \LaraCrud\Contracts\ColumnContract
     */
    protected ColumnContract $columnContract;

    /**
     * TableCell constructor.
     *
     * @param \LaraCrud\Contracts\ColumnContract $columnContract
     */
    public function __construct(ColumnContract $columnContract)
    {
        $this->columnContract = $columnContract;
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->html;
    }
}
