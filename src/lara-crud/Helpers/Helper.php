<?php
/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

namespace LaraCrud\Helpers;


use DbReader\Database;

trait Helper
{
    public $errors = [];

    /**
     * If table name mistyped and then tell user that table not found and show him a list of table.
     *
     * @param type $table
     * @return boolean
     * @throws \Exception
     */
    public static function checkMissingTable($table)
    {
        $insertAbleTable = !is_array($table) ? [$table] : $table;
        $availableTables = (new Database())->tables();
        $missingTable = array_diff($insertAbleTable, $availableTables);

        if (!empty($missingTable)) {
            $message = implode(",", $missingTable) . ' tables not found in ' . "\n" . implode("\n", $availableTables);
            throw new \Exception($message);
        }
        return true;

    }

    public static function loopTables(\Closure $closure)
    {
        $availableTables = (new Database())->tables();
        foreach ($availableTables as $availableTable) {
            $closure($availableTable);
        }
    }

    /**
     * Parse user defined model name and extract namespace and class name
     * @param $name
     * @return void
     */
    public function parseName($name)
    {
        if (strpos($name, "/", 1) !== FALSE) {
            $narr = explode("/", trim($name, "/"));
            $this->modelName = $this->getModelName(array_pop($narr));

            foreach ($narr as $path) {
                $this->namespace .= '\\' . ucfirst($path);
            }
        } else {
            $this->modelName = $this->getModelName($name);
        }
    }

    /**
     * Convert table name to Laravel Standard Model Name
     * For example users table become User.
     * Plural to singular and snakeCase to camelCase
     *
     * @param type $name
     * @return type
     */
    public function getModelName($name)
    {
        $name = $this->getSingular($name);
        return ucfirst(camel_case($name));
    }

    /**
     * Generally laravel use plural version of model name  as table name.
     * For example products table will be Product as Model name
     * @param string $words
     * @return string
     */
    public function getSingular($words)
    {
        return str_singular($words);
    }

    /**
     * Convert NS to path and then check if it exists if not then create it. Then return full specified path of the class.
     * @param string $extension
     * @return mixed
     */
    public function checkPath($extension = ".php")
    {
        //If model path does not exists then create model path.
        $this->ensurePath($this->namespace);
        return base_path($this->toPath($this->namespace) . '/' . $this->modelName . $extension);
    }

    public function modelPath($namespace, $extension = ".php")
    {
        //If model path does not exists then create model path.
        $this->ensurePath($namespace);
        return base_path($this->toPath($namespace) . '/' . $this->modelName . $extension);
    }

    public function traitPath($namespace, $extension = '.php')
    {
        $this->ensurePath($namespace);
        return base_path($this->toPath($namespace) . '/' . $this->traitName . $extension);
    }

    public function abstractPath($namespace, $extension = '.php')
    {
        $this->ensurePath($namespace);
        return base_path($this->toPath($namespace) . '/' . $this->abstractName . $extension);
    }

    /**
     * Create dir from namespace
     * @param $namespace
     */
    public function ensurePath($namespace)
    {
        $fullPath = base_path($this->toPath($namespace));
        if (!file_exists($fullPath)) {
            $relPath = $this->toPath($namespace);
            $nextPath = '';
            $folders = explode("/", $relPath);
            foreach ($folders as $folder) {
                $nextPath .= !empty($nextPath) ? "/" . $folder : $folder;
                if (!file_exists(base_path($nextPath))) {
                    mkdir(base_path($nextPath));
                }
            }
        }
    }

    /**
     * @param $namespace string Full Qualified namespace e.g. App\Http\Controllers
     * @return string will be return like app/Http/Controllers
     */
    public function toPath($namespace)
    {
        $nsArr = explode('\\', trim($namespace, "\\"));
        $rootNs = array_shift($nsArr);
        $loadComposerJson = new \SplFileObject(base_path('composer.json'));
        $composerArr = json_decode($loadComposerJson->fread($loadComposerJson->getSize()), true);
        $psr4 = isset($composerArr['autoload']['psr-4']) ? $composerArr['autoload']['psr-4'] : [];
        $rootPath = isset($psr4[$rootNs . "\\"]) ? $psr4[$rootNs . "\\"] : lcfirst($rootNs);
        return rtrim($rootPath, "/") . "/" . implode("/", $nsArr);
    }

    /**
     * Get Controller File and Class Name
     * @param string $name Default Name. It will be used if user does not provide any name.
     * @return string
     */
    public function getFileName($name)
    {
        if (!empty($this->fileName)) {
            return str_replace(".php", "", $this->fileName);
        }
        return $name;
    }

    /**
     * Return Full Qualify namespace
     * @param $namespace
     * @return string
     */
    public function getFullNS($namespace)
    {
        $rootNs = config('laracrud.rootNamespace', 'App');
        if (substr_compare($namespace, $rootNs, 0, strlen($rootNs)) !== 0) {
            return trim($rootNs, "\\") . "\\" . $namespace;
        }
        return $namespace;
    }

}