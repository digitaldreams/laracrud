<?php

namespace LaraCrud\Repositories\View;

use LaraCrud\Contracts\View\TableContract;
use LaraCrud\Helpers\TemplateManager;

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
        return new TemplateManager('views/4/table.html', [
            'tableHeader' => $this->generateTableHeader(),
        ]);
    }

    private function generateTableHeader(): string
    {

    }

    /**
     * @return array
     */
    public function columns(): array
    {
        $data = [];
        foreach ($this->table->columns() as $columnRepository) {
            if ($columnRepository->dataType()) {
                
            }
        }
    }

    public function links(): string
    {
    }
}
