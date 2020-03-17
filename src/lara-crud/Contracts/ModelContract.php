<?php


namespace LaraCrud\Contracts;

interface ModelContract
{
    /**
     * @return string
     */
    public function shortName(): string;

    /**
     * @return string
     */
    public function fullName(): string;

    /**
     * @return TableContract
     */
    public function table(): TableContract;

    /**
     * @return bool
     */
    public function isSoftDelete(): bool;

    /**
     * @return array|null
     */
    public function searchable(): ?array;

    /**
     * @return array|null
     */
    public function relations(): ?array;

    /**
     * @return bool
     */
    public function downloadable(): bool;

    /**
     * @return bool
     */
    public function uploadable(): bool;

    /**
     * @return array
     */
    public function getFillables(): array;

    /**
     * @return array
     */
    public function getProtected(): array;
}
