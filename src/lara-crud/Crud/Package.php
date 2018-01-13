<?php
/**
 * Created by PhpStorm.
 * User: digitaldreams
 * Date: 11/01/18
 * Time: 14:31
 */

namespace LaraCrud\Crud;


use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;

class Package
{
    use Helper;
    /**
     * @var
     */
    protected $name;
    /**
     * @var
     */
    protected $namespace;
    /**
     * @var \Illuminate\Config\Repository|mixed|string
     */
    protected $rootPath = '';
    /**
     * @var string
     */
    protected $packagePath = '';

    protected $templatePath = '';

    /**
     * @var \RecursiveDirectoryIterator
     */
    protected $dirIt;

    protected $dirTree = [];

    /**
     * Package constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = strtolower($name);
        $this->rootPath = rtrim(config('laracrud.package.path'), "/");
        $this->packagePath = $this->rootPath . "/" . $this->name;
        $this->namespace = $this->getModelName($name);
        $this->templatePath = $this->getFullPath('package');
        $this->dirIt = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->templatePath), \RecursiveIteratorIterator::SELF_FIRST);
        $this->dirTree = $this->fetchDirs();
    }

    /**
     *
     */
    public function save()
    {
        $rootPath = $this->rootPath;

        if (!file_exists($rootPath)) {
            mkdir($rootPath);
        }

        if (!file_exists($this->packagePath)) {
            mkdir($this->packagePath);
        }

        foreach ($this->dirTree as $name => $splFileinfo) {

            if ($splFileinfo->isDir()) {
                if (!file_exists($this->packagePath . "/" . $name)) {
                    mkdir($this->packagePath . "/" . $name);
                }
            } else {
                $content = '';
                if ($splFileinfo->getSize() > 0) {
                    $temp = new TemplateManager("package/" . $name, $this->globalVars());
                    $content = $temp->get();
                }
                $this->saveFile($name, $content);
            }
        }
    }

    /**
     * @return array
     */
    protected function globalVars()
    {

        $data = [
            'packageNamespace' => $this->namespace,
            'packageName' => $this->name
        ];
        return $data;

    }

    /**
     *
     */
    protected function fetchDirs()
    {
        $dirTree = [];
        foreach ($this->dirIt as $name => $it) {
            if (in_array($it->getBasename(), [".", ".."])) {
                continue;
            }
            $dirTree[$this->getRelativeDirectoryName($it) . $it->getBasename()] = $it;

        }
        return $dirTree;
    }

    /**
     * @param $it
     * @return mixed
     */
    protected function getRelativeDirectoryName($it)
    {
        $path = str_replace($this->templatePath, "", $it->getPathname());
        return str_replace($it->getBaseName(), "", trim($path, "/"));
    }

    /**
     * @param $filePath
     * @param $content
     * @return bool
     */
    protected function saveFile($filePath, $content)
    {
        $filePath=$this->processFileName($filePath);

        $fullPath = $this->packagePath . "/" . $filePath;
        if (file_exists($fullPath)) {
            return false;
        }
        $splFileObject = new \SplFileObject($fullPath, 'w+');
        $splFileObject->fwrite($content);
        $splFileObject->fflush();
        return $splFileObject;
    }

    /**
     * It will search given template in resources/vendor/$name if not found then search in  current directory template folder
     *
     * @param $filename
     * @return full qualified file path if found otherwise false;
     * @internal param string $name Template Name
     *
     */
    public function getFullPath($filename)
    {
        $retPath = false;
        if (file_exists(resource_path('views/vendor/laracrud/templates/' . $filename))) {
            $retPath = resource_path('views/vendor/laracrud/templates/' . $filename);
        } elseif (file_exists(__DIR__ . '/../../../resources/templates/' . $filename)) {
            $retPath = __DIR__ . '/../../../resources/templates/' . $filename;
        }
        return $retPath;
    }

    protected function processFileName($filename)
    {
        if(strripos($filename,".php.txt")){
          return str_replace(".txt","",$filename);
        }
        return $filename;
    }

}