<?php

namespace LaraCrud\Builder\Controller;

use LaraCrud\Contracts\Controller\ViewAbleMethod;

trait BladeViewTrait
{
    protected $breadCrumbs = [];

    protected $toolsMenu = [];

    protected $path = null;

    protected $fileName = null;

    /**
     *
     * @return mixed
     */
    public function addBreadcrumb(string $link, string $text, ?int $position = null)
    {
        $this->breadCrumbs[] = [
            'link' => $link,
            'text' => $text,
            'position' => $position,
        ];

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(): ViewAbleMethod
    {
    }

    public function setFileName(): ViewAbleMethod
    {
    }

    public function getFileName(): string
    {
    }

    public function addToolMenuItem(string $link, string $text, ?string $icon = null): ViewAbleMethod
    {
    }

    public function setTitle(): ViewAbleMethod
    {
    }

    /**
     * @param          $parent
     *
     */
    public function AddToSideBarMenu($parent, ?int $postion = null): ViewAbleMethod
    {
    }
}
