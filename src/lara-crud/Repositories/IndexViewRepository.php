<?php


namespace LaraCrud\Repositories;

use Illuminate\Database\Eloquent\Model;
use LaraCrud\Contracts\IndexViewContract;
use LaraCrud\Contracts\TableContract;

class IndexViewRepository implements IndexViewContract
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var TableContract
     */
    protected $table;

    /**
     * IndexViewRepository constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->table = app(TableContract::class, ['table' => $this->model->getTable()]);
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return $this->table->label();
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
        return 'index';
    }

    public function path(): string
    {

    }

    public function title(): string
    {
        return $this->table->label();
    }

    public function table(): TableContract
    {
        return $this->table;
    }

}
