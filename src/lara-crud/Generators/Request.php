<?php

namespace LaraCrud\Generators;

use Illuminate\Database\Eloquent\Model;
use LaraCrud\Contracts\ClassGeneratorContract;
use LaraCrud\Contracts\TableContract;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\FileSave;
use LaraCrud\Helpers\NamespaceResolver;
use LaraCrud\Helpers\TemplateManager;

class Request implements ClassGeneratorContract
{
    use Helper, FileSave;

    protected TableContract $table;

    protected Model $model;

    /**
     * Request Class name. Defaut is single version of database table.
     *
     */
    protected string $modelName;

    /**
     * Request Class parent Namespace.
     *
     */
    protected string $namespace;

    /**
     * Template path either api or web.
     *
     */
    protected string $template;

    protected string $authorization = 'true';

    public function __construct(Model $model, ?string $name = '', ?bool $api = false)
    {
        $this->model = $model;
        $this->table = app()->make(TableContract::class, ['table' => $model->getTable()]);

        $this->namespace = NamespaceResolver::getRequestRoot($api);
        $this->modelName = $this->getModelName((new \ReflectionClass($this->model))->getShortName());
        if (!empty($name)) {
            $this->parseName($name);
        } else {
            $this->modelName .= config('laracrud.request.classSuffix');
        }
        $this->template = $api === true ? 'api' : 'web';
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

    public function makeRules(): array
    {
        $rules = [];
        $columns = $this->table->columns();
        $fillable = $this->model->getFillable();
        $guarded = $this->model->getGuarded();
        foreach ($columns as $column) {
            if (
                !$column->isFillable() || !in_array($column->name(), $fillable) || in_array(
                    $column->name(),
                    $guarded
                )
            ) {
                continue;
            }
            $rules[] = "\t\t\t'{$column->name()}' => " . $this->implode($column->validationRules());
        }

        return $rules;
    }

    public function setAuthorization(string $auth): self
    {
        $this->authorization = $auth;

        return $this;
    }

    private function implode(array $rules): string
    {
        $string = '[' . "\n\t\t\t\t";
        foreach ($rules as $rule) {
            $string .= 0 !== substr_compare((string)$rule, 'Rule::', 0, 6) ? "'" . $rule . "'" : $rule;
            $string .= ',' . "\n\t\t\t\t";
        }
        $string .= '],';

        return $string;
    }

    public function getClassName(): string
    {
        return $this->modelName;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
