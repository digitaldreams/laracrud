<?php


namespace LaraCrud\Helpers;


class ClassInspector
{
    protected $name;

    protected $shortName;
    protected $description;
    protected $publicMethods;
    protected $protectedMethods;
    protected $privateMethods;
    protected $constants;
    protected $properties;

    /**
     * @var \ReflectionClass
     */
    public $reflection;

    public function __construct($name)
    {
        $this->name = $name;
        $this->reflection = new \ReflectionClass($this->name);
        $this->fetchMethods();
        $this->properties = $this->reflection->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
        $this->constants = $this->reflection->getConstants();
        $this->description = $this->reflection->getDocComment();
        $this->shortName = $this->reflection->getShortName();
    }

    private function fetchMethods()
    {
        $this->publicMethods = $this->filterMethod($this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC));
        $this->protectedMethods = $this->filterMethod($this->reflection->getMethods(\ReflectionMethod::IS_PROTECTED));
        $this->privateMethods = $this->filterMethod($this->reflection->getMethods(\ReflectionMethod::IS_PRIVATE));
        $this->publicMethods = $this->filterMethod($this->reflection->getMethods(\ReflectionMethod::IS_PUBLIC));
        return $this;

    }

    /**
     * Child class all the method of its parent. But we will accept only child class method.
     *
     * @param string $controllerName
     * @param string $reflectionMethods
     * @return array ReflectionMethod class
     */
    protected function filterMethod($reflectionMethods)
    {
        $retMethods = [];
        foreach ($reflectionMethods as $method) {
            if (substr_compare($method->name, '__', 0, 2) != 0 && $method->class == $this->name) {
                $retMethods[] = $method->name;
            }
        }
        return $retMethods;
    }

    /**
     * @param $method
     * @return array
     * @throws \ReflectionException
     */
    public function prepareMethodArgs($method)
    {
        $args = [];
        $reflectionMethod = new \ReflectionMethod($this->name, $method);
        foreach ($reflectionMethod->getParameters() as $param) {
            if ($param->getClass()) {
                if (is_subclass_of($param->getClass()->name, \Illuminate\Http\Request::class) || $param->getClass()->name == \Illuminate\Http\Request::class) {
                    $requestClass = $param->getClass()->name;
                    $args[] = new $requestClass;
                } elseif (is_subclass_of($param->getClass()->name, \Illuminate\Database\Eloquent\Model::class)) {
                    $modelClass = $param->getClass()->name;
                    $args[] = new $modelClass;
                }
            } else {
                $args[] = '';
            }
        }
        return $args;
    }

    public function __get($name)
    {
        return property_exists($this, $name) ? $this->{$name} : false;
    }
}