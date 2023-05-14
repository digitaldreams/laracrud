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
     */
    public array $import = [];

    /**
     * @var array
     */
    public  $errors = [];

    /**
     * If table name mistyped and then tell user that table not found and show him a list of table.
     *
     * @param string $table
     *
     * @return bool
     * @throws \Exception
     *
     */
    public static function checkMissingTable($table)
    {
        $insertAbleTable = ! is_array($table) ? [$table] : $table;
        $availableTables = (new Database())->tables();
        $missingTable = array_diff($insertAbleTable, $availableTables);

        if (! empty($missingTable)) {
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
        if (str_contains(substr((string) $name, 1), '/')) {
            $narr = explode('/', trim((string) $name, '/'));
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
     * Get Controller File and Class Name.
     *
     * @param string $name Default Name. It will be used if user does not provide any name.
     *
     * @return string
     */
    public function getFileName($name)
    {
        if (! empty($this->fileName)) {
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


    /**
     * @return string
     */
    protected function makeNamespaceUseString()
    {
        $retStr = '';
        $ns = array_unique($this->import);
        foreach ($ns as $n) {
            $retStr .= 'use ' . $n . ';' . PHP_EOL;
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
