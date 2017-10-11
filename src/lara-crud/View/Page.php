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

    public function __construct()
    {
        $this->version = config('laracrud.view.bootstrap');
        $this->filePath = resource_path("views/{$this->folder}/{$this->name}" . ".blade.php");
    }

    /**
     * @throws \Exception
     */
    public function save()
    {
        if (file_exists($this->filePath)) {
            throw  new \Exception($this->name . ' already exists');
        }
        if (!file_exists(resource_path('views/' . $this->folder))) {
            mkdir(resource_path('views/' . $this->folder));
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
            if (!file_exists(resource_path('views/' . $pagePath))) {
                mkdir(resource_path('views/' . $pagePath));
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