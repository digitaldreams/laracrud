<?php

namespace LaraCrud\Contracts;

interface PageViewContract
{
    /**
     * @param string   $link
     * @param string   $text
     * @param int|null $position
     *
     * @return mixed
     */
    public function addBreadcrumb(string $link, string $text, ?int $position = null): ViewAbleMethod;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return \LaraCrud\Contracts\ViewAbleMethod
     */
    public function setPath(string $path): ViewAbleMethod;

    /**
     * @return \LaraCrud\Contracts\ViewAbleMethod
     */
    public function setFileName(): ViewAbleMethod;

    /**
     * @return string
     */
    public function getFileName(): string;

    /**
     * @param string      $link
     * @param string      $text
     * @param string|null $icon
     *
     * @return \LaraCrud\Contracts\ViewAbleMethod
     */
    public function addToolMenuItem(string $link, string $text, ?string $icon = null): ViewAbleMethod;

    /**
     * @return \LaraCrud\Contracts\ViewAbleMethod
     */
    public function setTitle(): ViewAbleMethod;

    public function name(): string;

    public function path(): string;

    public function title(): string;

    public function table(): TableContract;
}
