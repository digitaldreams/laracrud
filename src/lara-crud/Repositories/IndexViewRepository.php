<?php


namespace LaraCrud\Repositories;

use LaraCrud\Contracts\IndexViewContract;
use LaraCrud\Contracts\ModelContract;
use LaraCrud\Contracts\TableContract;

class IndexViewRepository implements IndexViewContract
{

    /**
     * @return string
     */
    public function label(): string
    {
        // TODO: Implement label() method.
    }

    /**
     * @return string|null
     */
    public function searchForm(): ?string
    {
        // TODO: Implement searchForm() method.
    }

    /**
     * @return string|null
     */
    public function recycleBin(): ?string
    {
        // TODO: Implement recycleBin() method.
    }

    /**
     * Either form or modal.
     *
     * @return string
     */
    public function editType(): string
    {
        // TODO: Implement editType() method.
    }

    /**
     * Either Table or Card.
     *
     * @return string
     */
    public function displayType(): string
    {
        // TODO: Implement displayType() method.
    }

    public function name(): string
    {
        // TODO: Implement name() method.
    }

    public function path(): string
    {
        // TODO: Implement path() method.
    }

    public function title(): string
    {
        // TODO: Implement title() method.
    }

    public function table(): TableContract
    {
        // TODO: Implement table() method.
    }

    public function model(): ModelContract
    {
        // TODO: Implement model() method.
    }
}
