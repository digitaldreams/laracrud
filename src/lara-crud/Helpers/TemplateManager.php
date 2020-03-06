<?php

namespace LaraCrud\Helpers;

/**
 * Class TemplateManager
 * All the internal template used by this library will be read and write via this class.
 * @package LaraCrud
 */
class TemplateManager
{
    protected $path;

    /**
     * @var \SplFileObject
     */
    protected $file;

    /**
     * Fully processed template string
     *
     * @var string
     */
    protected $template = '';

    protected $startTag = '@@';

    protected $endTag = '@@';

    /**
     * Associative array of placeholders. Where index is placeholder without tag
     *
     * @var array
     */
    protected $data = [];

    public function __construct($filename, $data = [], $autoProcess = true)
    {
        $this->file = new \SplFileObject($this->getFullPath($filename), 'r');
        $this->template = $this->file->fread($this->file->getSize());
        $this->data = $data;

        if ($autoProcess) {
            $this->process();
        }
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
        return $this->path = $retPath;
    }

    /**
     * return full qualify file path
     *
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * This function will replace its placeholder by its original value and return the processed template
     *
     * Here index will be placeholder name without prefix and
     * value will be its value which will be replace instead of placeholder
     *
     * @return string
     */
    public function process()
    {
        return $this->template = strtr($this->template, $this->placeholders());
    }

    protected function placeholders()
    {
        $retArr = [];
        foreach ($this->data as $index => $value) {
            $retArr[$this->startTag . $index . $this->endTag] = $value;
        }
        return $retArr;
    }


    /**
     * Set Template Tags
     *
     * @param string $start
     * @param string $end
     * @return $this
     */
    public function setTags($start = '@@', $end = '@@')
    {
        $this->startTag = $start;
        $this->endTag = $end;
        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function get()
    {
        return $this->template;
    }

    /**
     * String representation of template class
     * @return string
     */
    public function __toString()
    {
        return $this->template;
    }

    /**
     * Your view path on config file will be appended before your given path or file
     *
     * @param $relativePath
     *
     * @return int number of bytes writeen or null or errork
     */
    public function save($relativePath)
    {
        $filepath = config('laracrud.view.path');
        $file = new \SplFileObject(base_path($filepath . $relativePath), 'w+');
        return $file->fwrite($this->template);
    }
}
