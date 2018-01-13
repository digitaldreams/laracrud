<?php
/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

namespace LaraCrud\View;

use DbReader\Table;
use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;

abstract Class Page implements Crud
{
    use Helper;
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var string
     */
    protected $name;

    /**
     * Type of page e.g. modal, table, tabpan
     * @var string
     */
    protected $type;

    /**
     * Name of the parent folder where file will be saved.
     * @var
     */
    protected $folder;

    /**
     * Bootstrap Version
     * @var
     */
    protected $version;

    /**
     * @var
     */
    protected $filePath;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    protected $resource_path;

    /**
     * Page constructor.
     */
    public function __construct()
    {
        $this->version = config('laracrud.view.bootstrap');
        $this->resource_path = config('laracrud.view.path');

        $this->filePath = rtrim($this->resource_path, "/") . "/" . $this->folder . "/" . $this->name . ".blade.php";
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        if (file_exists($this->filePath)) {
            throw  new \Exception($this->name . ' already exists');
        }
        $folder=rtrim($this->resource_path, "/") . "/" . $this->folder;
        if (!file_exists($folder)) {
            mkdir($folder);
        }
        $table = new \SplFileObject($this->filePath, 'w+');
        $table->fwrite($this->template());
    }

    /**
     *  Assign Folder Name
     */
    public function setFolderName()
    {
        $pagePath = config('laracrud.view.page.path');
        if (!empty($pagePath)) {
            $folder=rtrim(config('laracrud.view.path'), "/") . "/" . $pagePath;
            if (!file_exists($folder)) {
                mkdir($folder);
            }
            $this->folder = trim($pagePath, "/") . "/" . $this->table->name();
        } else {
            $this->folder = $this->table->name();
        }
    }

    /**
     * Whether current file exists or now.
     */
    public function isExists()
    {
        return file_exists($this->filePath);
    }

    /**
     * @return mixed
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


}