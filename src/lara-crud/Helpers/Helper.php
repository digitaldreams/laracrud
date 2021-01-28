<?php
/**
 * Tuhin Bepari <digitaldreams40@gmail.com>.
 */

namespace LaraCrud\Helpers;

use DbReader\Database;
use Illuminate\Support\Str;

trait Helper
{
    /**
     * Import Namespace for Usages.
     *
     * @var array
     */
    public $import = [];

    /**
     * @var array
     */
    public $errors = [];

    /**
     * If table name mistyped and then tell user that table not found and show him a list of table.
     *
     * @param string $table
     *
     * @throws \Exception
     *
     * @return bool
     */
    public static function checkMissingTable($table)
    {
        $insertAbleTable = !is_array($table) ? [$table] : $table;
        $availableTables = (new Database())->tables();
        $missingTable = array_diff($insertAbleTable, $availableTables);

        if (!empty($missingTable)) {
            $message = implode(',', $missingTable) . ' tables not found in ' . "\n" . implode("\n", $availableTables);

            throw new \Exception($message);
        }

        return true;
    }

    /**
     * Parse user defined model name and extract namespace and class name.
     *
     * @param $name
     *
     * @return void
     */
    public function parseName($name)
    {
        if (false !== strpos($name, '/', 1)) {
            $narr = explode('/', trim($name, '/'));
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
     * Plural to singular and snakeCase to camelCase.
     *
     * @param string $name
     *
     * @return string
     */
    public function getModelName($name)
    {
        $name = $this->getSingular($name);

        return ucfirst(Str::camel($name));
    }

    /**
     * Generally laravel use plural version of model name  as table name.
     * For example products table will be Product as Model name.
     *
     * @param string $words
     *
     * @return string
     */
    public function getSingular($words)
    {
        return Str::singular($words);
    }

    /**
     * Convert NS to path and then check if it exists if not then create it. Then return full specified path of the class.
     *
     * @param string $extension
     *
     * @return mixed
     */
    public function checkPath($extension = '.php')
    {
        //If model path does not exists then create model path.
        $fullPath = base_path($this->toPath($this->namespace));
        if (!file_exists($fullPath)) {
            $relPath = $this->toPath($this->namespace);
            $nextPath = '';
            $folders = explode('/', $relPath);
            foreach ($folders as $folder) {
                $nextPath .= !empty($nextPath) ? '/' . $folder : $folder;
                if (!file_exists(base_path($nextPath))) {
                    mkdir(base_path($nextPath));
                }
            }
        }

        return base_path($this->toPath($this->namespace) . '/' . $this->modelName . $extension);
    }

    /**
     * @param string $namespace Full Qualified namespace e.g. App\Http\Controllers
     *
     * @return string will be return like app/Http/Controllers
     */
    public function toPath($namespace)
    {
        $nsArr = explode('\\', trim($namespace, '\\'));
        $rootNs = array_shift($nsArr);
        $loadComposerJson = new \SplFileObject(base_path('composer.json'));
        $composerArr = json_decode($loadComposerJson->fread($loadComposerJson->getSize()), true);
        $psr4 = isset($composerArr['autoload']['psr-4']) ? $composerArr['autoload']['psr-4'] : [];
        $rootPath = isset($psr4[$rootNs . '\\']) ? $psr4[$rootNs . '\\'] : lcfirst($rootNs);

        return rtrim($rootPath, '/') . '/' . implode('/', $nsArr);
    }

    /**
     * Get Controller File and Class Name.
     *
     * @param string $name Default Name. It will be used if user does not provide any name.
     *
     * @return string
     */
    public function getFileName($name)
    {
        if (!empty($this->fileName)) {
            return str_replace('.php', '', $this->fileName);
        }

        return $name;
    }

    /**
     * Return Full Qualify namespace.
     *
     * @param $namespace
     *
     * @return string
     */
    public function getFullNS($namespace)
    {
        $rootNs = config('laracrud.rootNamespace', 'App');
        if (empty($namespace)) {
            return $rootNs;
        }
        if (0 !== substr_compare($namespace, $rootNs, 0, strlen($rootNs))) {
            return trim($rootNs, '\\') . '\\' . $namespace;
        }

        return $namespace;
    }

    /**
     * @return string
     */
    protected function makeNamespaceUseString()
    {
        $retStr = '';
        $ns = array_unique($this->import);
        foreach ($ns as $namespace) {
            $retStr .= 'use ' . $namespace . ';' . PHP_EOL;
        }

        return $retStr;
    }

    /**
     * Get full newly created fully qualified Class namespace.
     */
    public function getFullName()
    {
        return $this->namespace . '\\' . $this->fileName;
    }
}
