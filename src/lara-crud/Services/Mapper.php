<?php

namespace LaraCrud\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\NamespaceResolver;

class Mapper
{
    use Helper;

    protected string $fileName;
    protected array $data = [];

    protected Model $model;
    protected array $defaultArray = [
        'table' => '',
        'model' => '',
        'modelNamespace' => '',
        'controller' => '',
        'controllerNamespace' => '',
        'policy' => '',
        'policyNamespace' => '',
        'apiResource' => '',
        'apiResourceNamespace' => '',
        'storeRequest' => '',
        'updateRequest' => '',
        'createdAt' => '',
        'updatedAt' => '',
    ];
    public static string $folder = "laracrud";

    protected function __construct(array $data = [], string $fileName = '')
    {
        $this->data = $data;
        $this->fileName = static::$folder . '/' . $fileName;
    }

    public static function loadByModel(string|Model $model, array $data = []): static
    {
        if (is_string($model)) {
            $modelNamespace = NamespaceResolver::getFullNS($model);
            if (!class_exists($modelNamespace)) {
                throw new ModelNotFoundException(sprintf('%s Model not found', $model));
            }
            $model = new $modelNamespace;
        }
        $tableName = $model->getTable();

        return static::loadByTable($tableName, $data);
    }

    public static function loadByTable(string $tableName, array $data = []): static
    {
        $fileName = $tableName . '.json';
        $path = static::$folder . '/' . $fileName;
        $data['table'] = $tableName;

        if (Storage::has($path)) {
            $savedData = json_decode(Storage::get($path), true);
            $data['updatedAt']=date('c');
            return new static(static::mergeData($savedData, $data), $fileName);
        }
        $data['createdAt']= date('c');

        return new static($data, $fileName);
    }

    private static function mergeData(array $savedData, array $newData): array
    {
        foreach ($savedData as $key => $value) {
            if (!empty($value) && (!isset($newData[$key]) || (array_key_exists($key, $newData) && empty($newData[$key])))) {
                $newData[$key] = $value;
            }
        }

        return $newData;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function get(string $key, string|bool $default = false): string|bool|array
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, string|array $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function save()
    {
        $data = array_merge($this->defaultArray, $this->data);
        Storage::put($this->fileName, json_encode($data, JSON_PRETTY_PRINT));
    }
}
