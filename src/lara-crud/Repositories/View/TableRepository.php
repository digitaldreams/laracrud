<?php

namespace LaraCrud\Repositories\View;

use LaraCrud\Contracts\View\TableContract;

class TableRepository extends PageRepository implements TableContract
{

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
        // TODO: Implement template() method.
    }

    public function columns(): array
    {
        // TODO: Implement columns() method.
    }

    public function links(): string
    {

    }

}
