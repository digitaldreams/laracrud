<?php

namespace LaraCrud\Crud;

class ApiController extends Controller
{
    protected $transformerName;

    public function __construct($model, $name = '', $only = '', $parent = false)
    {
        $this->transformerName = $this->getTransformerClass();
        parent::__construct($model, $name, $only, true, $parent);
    }

    /**
     * Get Transformer Class.
     */
    protected function getTransformerClass()
    {
        $transformerNs = $this->getFullNS(config('laracrud.transformer.namespace', 'Transformers'));
        $suffiex = config('laracrud.transformer.classSuffix', 'Transformer');
        $transformerName = $this->shortModelName.$suffiex;
        $fullTransformerNs = $transformerNs.'\\'.$transformerName;
        $this->import[] = $fullTransformerNs;

        if (class_exists($fullTransformerNs)) {
            return $transformerName;
        } elseif (is_object($this->model)) {
            $transformerCrud = new Transformer($this->model);
            $transformerCrud->save();
        }

        return $transformerName;
    }

    /**
     * @param $requestClass
     *
     * @return array
     */
    protected function makeApiRequest($requestClass)
    {
        $rules = [];

        if (!class_exists($requestClass)) {
            $requestClass = $this->requestFolderNs.'\\'.$requestClass;
        }

        if (is_subclass_of($requestClass, \Dingo\Api\Http\FormRequest::class)) {
            $request = new $requestClass();
            $rules = $request->rules();
        }

        return !empty($rules) && is_array($rules) ? json_encode($rules, JSON_PRETTY_PRINT) : '{}';
    }
}
