<?php

namespace LaraCrud\Contracts;

interface ColumnContract
{
    public function isPk(): bool;

    public function name(): string;

    public function label(): string;

    public function table(): TableContract;

    public function isFillable(): bool;

    public function isRequired(): bool;

    public function isNull(): bool;

    public function isUnique(): bool;

    public function dataType(): string;
    public function phpDataType(): string;

    public function inputType(): string;

    /**
     * @return mixed
     */
    public function length();

    public function order(): ?int;

    public function setOrder(int $number): self;

    /**
     * @return mixed
     */
    public function defaultValue();

    public function image(): string;

    public function file(): string;

    public function options(): ?array;

    /**
     * @return \LaraCrud\Helpers\ForeignKey|null
     */
    public function foreignKey();

    /**
     * Request validation Rules.
     */
    public function validationRules(): array;
}
