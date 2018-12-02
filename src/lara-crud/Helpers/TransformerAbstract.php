<?php

namespace LaraCrud\Helpers;


use Illuminate\Database\Eloquent\Model;
use League\Fractal\TransformerAbstract as RootTransformer;

class TransformerAbstract extends RootTransformer
{
    protected $fields;

    public function __construct($fields = [])
    {
        $this->fields = $fields;
    }

    /**
     * @param $data
     * @return array
     */
    protected function filterFields($data)
    {
        return is_array($this->fields) && !empty($this->fields) ? array_intersect_key($data, array_flip($this->fields)) : $data;
    }

    /**
     * @param $include
     * @return $this
     */
    public function addDefaultInclude($include)
    {
        if (is_string($include) && in_array($include, $this->availableIncludes)) {
            $this->defaultIncludes[] = $include;
        } elseif (is_array($include) && !empty($include)) {
            $this->defaultIncludes[] = array_unique(array_merge($this->defaultIncludes, $include));
        }
        return $this;
    }

}