<?php

namespace LaraCrud\Contracts;

/**
 * Tuhin Bepari <digitaldreams40@gmail.com>.
 */
interface FileGeneratorContract
{
    /**
     * Process template and return complete code.
     *
     * @return mixed
     */
    public function template();

    /**
     * Get code and save to disk.
     *
     * @return mixed
     */
    public function save();

}
