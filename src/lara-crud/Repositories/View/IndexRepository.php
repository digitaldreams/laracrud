<?php

namespace LaraCrud\Repositories\View;

use Illuminate\Support\Str;
use LaraCrud\Contracts\View\CardContract;
use LaraCrud\Contracts\View\IndexContract;
use LaraCrud\Contracts\View\TableContract;
use LaraCrud\Helpers\TemplateManager;

class IndexRepository extends PageRepository implements IndexContract
{
    /**
     * @var \LaraCrud\Repositories\View\TableRepository
     */
    protected object $displayType;

    public function searchForm(): ?string
    {
        if ($this->isSearchAble()) {
            return (new TemplateManager('view/4/search.html'))->get();
        }

        return '';
    }

    /**
     * @param \LaraCrud\Repositories\View\TableRepository $displayType
     *
     * @return \LaraCrud\Repositories\View\IndexRepository
     *
     * @throws \Exception
     */
    public function setDisplayType(object $displayType)
    {
        if ($displayType instanceof CardContract || $displayType instanceof TableContract) {
            $this->displayType = $displayType;

            if (!$this->displayType->isExists()) {
                $this->displayType->save();
            }

            return $this;
        }

        throw new \Exception($displayType . ' display type not supported.');
    }

    public function recycleBin(): ?string
    {
        // TODO: Implement recycleBin() method.
    }

    /**
     * Either table or card.
     *
     * @return string
     */
    public function displayType(): object
    {
        return $this->displayType;
    }

    /**
     * @throws \ReflectionException
     */
    public function path(): string
    {
        return 'pages.' . Str::plural($this->getModelShortName()) . '.index';
    }

    /**
     * @return string
     *
     * @throws \ReflectionException
     */
    private function body()
    {
        if ($this->displayType instanceof TableRepository) {
            return "@include('{$this->viewNamespace}tables.{$this->getModelShortName()}')";
        }
    }

    /**
     * @throws \ReflectionException
     */
    public function template(): string
    {
        return new TemplateManager('view/4/pages/index.html', [
            'searchForm' => $this->searchForm(),
            'body' => $this->body(),
            'title' => $this->table->label(),
        ]);
    }
}
