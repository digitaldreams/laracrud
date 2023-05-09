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
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelRelationReader
{
    private readonly \ReflectionClass $reflectionClass;

    protected array $single = [];

    protected array $collection = [];

    /**
     * Whether model has a user belongs to relation or not.
     *
     * @var bool
     */
    protected bool $hasOwner = false;

    protected string $ownerForeignKey = '';

    protected string $ownerLocalKey = '';

    protected string $shortName;

    /**
     * ModelRelationReader constructor.
     */
    public function __construct(protected Model $model)
    {
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
        $this->shortName = $reflectionClass->getShortName();
        $methods = $reflectionClass->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            try {
                if ($method->getNumberOfParameters() == 0 && $method->class == $this->model::class) {
                    $response = $method->invoke($this->model);
                    if (! is_object($response)) {
                        continue;
                    }
                    $responseClass = $response::class;
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
            } catch (\Exception) {
                continue;
            }
        }

        return $this;
    }

    /**
     * Get relation that return a single Model instance.
     */
    public function getSingleRelations(): array
    {
        return $this->single;
    }

    /**
     * Get relations that return a collection of Models
     */
    public function getCollectionRelations(): array
    {
        return $this->collection;
    }

    /**
     * Get all Relationships methods and its response defined in Model.
     */
    public function getAll(): array
    {
        return array_merge($this->single, $this->collection);
    }


    /**
     * @param $responseClass
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
     * @param \Illuminate\Database\Eloquent\Relations\BelongsTo $relation
     */
    protected function findOwner(Relation $relation): bool
    {
        if ($relation instanceof BelongsTo) {
            $userModel = config('auth.providers.users.model');
            if ($userModel == $relation->getQuery()->getModel()::class) {
                $this->ownerForeignKey = $relation->getForeignKeyName();
                $this->ownerLocalKey = $relation->getOwnerKeyName();
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

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function isSoftDeleteAble(): bool
    {
        return in_array(SoftDeletes::class, class_uses($this->model));
    }
}
