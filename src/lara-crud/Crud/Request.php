<?php

namespace LaraCrud\Crud;

use Illuminate\Database\Eloquent\Model;
use LaraCrud\Contracts\Crud;
use LaraCrud\Contracts\TableContract;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;

class Request implements Crud
{
    use Helper;

    /**
     * @var \LaraCrud\Contracts\TableContract
     */
    protected $table;

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Request Class name. Defaut is single version of database table.
     *
     * @var string
     */
    protected $modelName;

    /**
     * Request Class parent Namespace.
     *
     * @var string
     */
    protected $namespace;

    /**
     * Template path either api or web.
     *
     * @var string
     */
    protected $template;

    protected $authorization = 'true';

    /**
     * RequestCrud constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string|null                         $name
     * @param bool                                $api
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function __construct(Model $model, ?string $name = '', ?bool $api = false)
    {
        $this->model = $model;
        $this->table = app()->make(TableContract::class, ['table' => $model->getTable()]);

        $this->namespace = ! empty($api) ? config('laracrud.request.apiNamespace') : config('laracrud.request.namespace');
        $this->namespace = $this->getFullNS($this->namespace);
        $this->modelName = $this->getModelName((new \ReflectionClass($this->model))->getShortName());
        if (! empty($name)) {
            $this->parseName($name);
        } else {
            $this->modelName .= config('laracrud.request.classSuffix');
        }
        $this->template = ! empty($api) ? 'api' : 'web';
    }

    /**
     * Save code to file.
     */
    public function save()
    {
        $filePath = $this->checkPath();
        if (file_exists($filePath)) {
            throw new \Exception($this->namespace . '\\' . $this->modelName . ' already exists');
        }
        $model = new \SplFileObject($filePath, 'w+');
        $model->fwrite($this->template());
    }

    /**
     * Generate complete code.
     *
     * @return string
     */
    public function template()
    {
        $tempMan = new TemplateManager('request/' . $this->template . '/template.txt', [
            'namespace' => $this->namespace,
            'requestClassName' => $this->modelName,
            'authorization' => $this->authorization,
            'rules' => implode("\n", $this->makeRules()),
        ]);

        return $tempMan->get();
    }

    /**
     * @return array
     */
    public function makeRules()
    {
        $rules = [];
        $columns = $this->table->columns();
        $fillable = $this->model->getFillable();
        $guarded = $this->model->getGuarded();
        foreach ($columns as $column) {
            if (! $column->isFillable() || ! in_array($column->name(), $fillable) || in_array($column->name(),
                    $guarded)) {
                continue;
            }
            $rules[] = "\t\t\t'{$column->name()}' => " . $this->implode($column->validationRules());
        }

        return $rules;
    }

    /**
     * @param $auth
     *
     * @return $this
     */
    public function setAuthorization($auth)
    {
        $this->authorization = $auth;

        return $this;
    }

    private function implode(array $rules)
    {
        $string = '['."\n\t\t\t\t";
        foreach ($rules as $rule) {
            $string .= 0 !== substr_compare($rule, 'Rule::', 0, 6) ? "'" . $rule . "'" : $rule;
            $string .= ',' . "\n\t\t\t\t";
        }
        $string .= '],';

        return $string;
    }
}
