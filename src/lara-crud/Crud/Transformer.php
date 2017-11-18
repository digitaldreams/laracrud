<?php

namespace LaraCrud\Crud;

use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\TemplateManager;


class Transformer implements Crud
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var string
     */
    protected $name;

    /**
     * parent namespace of the Transformer
     * @var string
     */
    protected $namespace;

    public function __construct(\Illuminate\Database\Eloquent\Model $model, $name = false)
    {
        $this->model = $model;
        $this->name = $name;
        $this->namespace = config('');

    }

    /**
     * Process template and return complete code
     * @return mixed
     */
    public function template()
    {
        return (new TemplateManager('transformer/tempalte.txt', []))->get();
    }

    /**
     * Get code and save to disk
     * @return mixed
     */
    public function save()
    {
        $filePath = $this->checkPath();
        if (file_exists($filePath)) {
            throw new \Exception('Transformer already exists');
        }
        $model = new \SplFileObject($filePath, 'w+');
        $model->fwrite($this->template());
    }
}