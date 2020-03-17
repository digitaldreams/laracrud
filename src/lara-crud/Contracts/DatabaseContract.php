<?php


namespace LaraCrud\Contracts;

interface DatabaseContract
{
    /**
     * Collection of TableContract.
     *
     * @return mixed
     */
    public function tables();

    /**
     * @param $table string
     * @return mixed
     */
    public function tableExists(string $table);
}
