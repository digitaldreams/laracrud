<?php

namespace LaraCrud\Services\Controller;

use LaraCrud\Contracts\ViewAbleMethod;

trait BladeViewTrait
{
    protected $breadCrumbs = [];

    protected $toolsMenu = [];

    protected $path = null;

    protected $fileName = null;

    /**
     * @param string   $link
     * @param string   $text
     * @param int|null $position
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

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return \LaraCrud\Contracts\ViewAbleMethod
     */
    public function setPath(): ViewAbleMethod
    {
    }

    /**
     * @return \LaraCrud\Contracts\ViewAbleMethod
     */
    public function setFileName(): ViewAbleMethod
    {
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
    }

    /**
     * @param string      $link
     * @param string      $text
     * @param string|null $icon
     *
     * @return \LaraCrud\Contracts\ViewAbleMethod
     */
    public function addToolMenuItem(string $link, string $text, ?string $icon = null): ViewAbleMethod
    {
    }

    /**
     * @return \LaraCrud\Contracts\ViewAbleMethod
     */
    public function setTitle(): ViewAbleMethod
    {
    }

    /**
     * @param          $parent
     * @param int|null $postion
     *
     * @return \LaraCrud\Contracts\ViewAbleMethod
     */
    public function AddToSideBarMenu($parent, ?int $postion = null): ViewAbleMethod
    {
    }
}
