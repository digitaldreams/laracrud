<?php
/**
 * Tuhin Bepari <digitaldreams40@gmail.com>
 */

namespace LaraCrud\Helpers;


use DbReader\Database;

trait Helper
{
    public $errors=[];
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

    public function toPath($namespace)
    {
        return lcfirst(str_replace("\\", "/", $namespace));
    }

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
     * Convert NS to path and then check if it exists if not then create it. Then return full specified path of the class.
     * @param string $extension
     * @return mixed
     */
    public function checkPath($extension = ".php")
    {
        //If model path does not exists then create model path.
        if (!file_exists(base_path($this->toPath($this->namespace)))) {
            mkdir(base_path($this->toPath($this->namespace)));
        }
        return base_path($this->toPath($this->namespace) . '/' . $this->modelName . $extension);
    }

    /**
     * Get Controller File and Class Name
     * @param string $name Default Name. It will be used if user does not provide any name.
     * @return type
     */
    public function getFileName($name)
    {
        if (!empty($this->fileName)) {
            return str_replace(".php", "", $this->fileName);
        }
        return $name;
    }

}