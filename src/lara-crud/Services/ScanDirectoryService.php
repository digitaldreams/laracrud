<?php

namespace LaraCrud\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ScanDirectoryService
{
    private $filepath;

    /**
     * ScanDirectoryJob constructor.
     *
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filepath = $filePath;
    }

    /**
     * @return array
     */
    public function scan()
    {
        $dir = new RecursiveDirectoryIterator($this->filepath);
        $rit = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::CHILD_FIRST);
        $files = [];
        foreach ($rit as $file) {
            /** @var \SplFileObject $file */
            if ($file->isFile() && in_array($file->getExtension(), ['php'])) {
                $files[] = $file->getRealPath();
            }
        }

        return $files;
    }
}
