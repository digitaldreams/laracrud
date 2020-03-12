<?php


namespace LaraCrud\Contracts;


interface TableContract
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return string
     */
    public function label(): string;

    /**
     * @return string
     */
    public function icon(): string;

    /**
     * @return object|null
     */
    public function model(): ?object;

    /**
     * @return array
     */
    public function columns(): array;

    /**
     * @return array
     */
    public function indexes(): array;

    /**
     * @return array
     */
    public function relations(): array;

    /**
     * @return array
     */
    public function references(): array;

    /**
     * @return array
     */
    public function fileColumns(): array;

    /**
     * @return bool
     */
    public function hasFile(): bool;

}
