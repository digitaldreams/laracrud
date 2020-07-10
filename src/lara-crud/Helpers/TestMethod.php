<?php

namespace LaraCrud\Helpers;

class TestMethod
{
    protected $controller;

    protected $method;

    protected $requestClass;

    protected $model;

    protected $parameters;

    protected $path;

    protected $reflection;

    protected $data = '[]';

    protected $http_verb = '';

    protected $template;

    /**
     * TestMethod constructor.
     *
     * @param array $arr
     * @param bool  $api
     *
     * @throws \Exception
     */
    public function __construct($arr = [], $api = false)
    {
        $this->controller = isset($arr['controller']) ? $arr['controller'] : false;
        $this->method = isset($arr['method']) ? $arr['method'] : false;
        $this->path = isset($arr['path']) ? $arr['path'] : false;
        $this->parameters = isset($arr['parameters']) ? $arr['parameters'] : [];
        $http_verb = isset($arr['http_verbs']) ? $arr['http_verbs'] : false;
        $this->setHttpVerb($http_verb);

        if (!class_exists($this->controller)) {
            throw new \Exception($this->controller . ' Class does not exists');
        }
        $this->reflection = new \ReflectionMethod($this->controller, $this->method);
        $this->template = !empty($api) ? 'api' : 'web';
    }

    /**
     * One method may have several params are some may have default values and some may not have.
     * we will inspect this params and define in routes respectively.
     *
     * @param string $controller
     * @param string $method
     *
     * @return string
     */
    public function addParams($controller, $method)
    {
        $params = '';
        $reflectionMethod = $this->reflection;

        foreach ($reflectionMethod->getParameters() as $param) {
            // print_r(get_class_methods($param));
            if ($param->getClass()) {
                continue;
            }
            $optional = true == $param->isOptional() ? '?' : '';
            $params .= '/{' . $param->getName() . $optional . '}';
        }

        return $params;
    }

    /**
     * @return string
     */
    public function template()
    {
        return (new TemplateManager('test/' . $this->template . '/' . $this->http_verb . '.txt', [
            'name' => ucfirst($this->method),
            'path' => $this->path,
            'data' => $this->data,
        ]))->get();
    }

    /**
     * @param $http_verb
     *
     * @return $this
     */
    private function setHttpVerb($http_verb)
    {
        if (is_array($http_verb)) {
            if (in_array('HEAD', $http_verb)) {
                $key = array_search('HEAD', $http_verb);
                unset($http_verb[$key]);
            }
            $this->http_verb = strtolower(array_shift($http_verb));
        } else {
            $this->http_verb = strtolower($http_verb);
        }

        return $this;
    }
}
