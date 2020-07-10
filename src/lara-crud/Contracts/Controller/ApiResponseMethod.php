<?php

namespace LaraCrud\Contracts\Controller;

interface ApiResponseMethod
{
    /**
     * @return string
     */
    public function method();

    /**
     * @return array|\Illuminate\Http\Resources\Json\Resource
     */
    public function response();
}
