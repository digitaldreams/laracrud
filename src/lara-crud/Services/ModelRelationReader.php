<?php


namespace LaraCrud\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\Relation;

class ModelRelationReader
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected Model $model;
    private \ReflectionClass $reflectionClass;

    protected array $single;
    protected array $collection;

    protected bool $hasOwner;

    protected string $ownerForeignKey;
    protected string $ownerLocalKey;

    /**
     * ModelRelationReader constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Read method's response and check whether its a Eloquent Relationship methods or not.
     *
     * @return $this
     * @throws \ReflectionException
     */
    public function read(): self
    {
        $reflectionClass = new \ReflectionClass($this->model);
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            try {
                if ($method->getNumberOfParameters() == 0 && $method->class == get_class($this->model)) {
                    $response = $method->invoke($this->model);
                    if (!is_object($response)) {
                        continue;
                    }
                    $responseClass = get_class($response);
                    if ($this->isItem($responseClass)) {
                        $this->findOwner($response);
                        $this->single[] = [
                            'method' => $method,
                            'relation' => $response,
                        ];
                    } elseif ($this->isCollection($responseClass)) {
                        $this->collection[] = [
                            'method' => $method,
                            'relation' => $response,
                        ];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $this;
    }

    /**
     * Get relation that return a single Model instance.
     *
     * @return array
     */
    public function getSingleRelations(): array
    {
        return $this->single;
    }

    /**
     * Get relations that return a collection of Models
     *
     * @return array
     */
    public function getCollectionRelations(): array
    {
        return $this->collection;
    }

    /**
     * Get all Relationships methods and its response defined in Model.
     *
     * @return array
     */
    public function getAll(): array
    {
        return array_merge($this->single, $this->collection);
    }


    /**
     * @param $responseClass
     *
     * @return bool
     */
    private function isItem($responseClass): bool
    {
        $item = [
            HasOne::class,
            BelongsTo::class,
            MorphOne::class,
        ];
        return in_array($responseClass, $item);
    }

    /**
     * @param $responseClass
     *
     * @return bool
     */
    private function isCollection($responseClass): bool
    {
        $collection = [
            HasMany::class,
            BelongsToMany::class,
            HasManyThrough::class,
            MorphMany::class,
        ];
        return in_array($responseClass, $collection);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Relations\BelongsTo $response
     */
    protected function findOwner(Relation $response)
    {
        if ($response instanceof BelongsTo) {
            $userModel = config('auth.providers.users.model');
            if ($userModel == get_class($response->getQuery()->getModel())) {
                $this->ownerForeignKey = $response->getForeignKeyName();
                $this->ownerLocalKey = $response->getOwnerKeyName();
                $this->hasOwner = true;
                return true;
            }
        }
        return false;
    }

    public function hasOwner(): bool
    {
        return $this->hasOwner;
    }

    public function getOwnerForeignKey(): string
    {
        return $this->ownerForeignKey;
    }

    public function getOwnerLocalKey(): string
    {
        return $this->ownerLocalKey;
    }
}
