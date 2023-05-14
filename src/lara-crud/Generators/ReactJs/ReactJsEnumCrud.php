<?php

namespace LaraCrud\Generators\ReactJs;

use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\TemplateManager;

class ReactJsEnumCrud implements Crud
{
    protected string $shortName;

    protected array $constants = [];

    /**
     * ReactJsEnumCrud constructor.
     *
     * @param $class
     *
     * @throws \ReflectionException
     */
    public function __construct($class)
    {
        $reflectionClass = new \ReflectionClass($class);
        $this->shortName = $reflectionClass->getShortName();
        $this->constants = $reflectionClass->getConstants(\ReflectionClassConstant::IS_PUBLIC);
    }

    public function template()
    {
        return (new TemplateManager('reactjs/enum.txt', [
            'constants' => $this->prepareConstant(),
        ]))->get();
    }

    protected function prepareConstant(): string
    {
        $str = '';

        foreach ($this->constants as $key => $value) {
            $fv = is_string($value) ? '"' . $value . '",' . "\n" : $value . ",\n";
            $str .= "\t" . $key . ': ' . $fv;
        }

        return $str;
    }

    public function save()
    {
        $fullPath = config('laracrud.reactjs.rootPath') . '/enums/' . $this->shortName . '.js';
        $migrationFile = new \SplFileObject($fullPath, 'w+');
        $migrationFile->fwrite($this->template());
    }
}
