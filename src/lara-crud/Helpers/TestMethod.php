<?php
/**
 * Created by PhpStorm.
 * User: Tuhin
 * Date: 12/18/2017
 * Time: 11:19 AM
 */

namespace LaraCrud\Helpers;


class TestMethod
{
    /**
     * One method may have several params are some may have default values and some may not have.
     * we will inspect this params and define in routes respectively
     *
     * @param string $controller
     * @param string $method
     * @return string
     */
    public function addParams($controller, $method)
    {
        $params = '';
        $reflectionMethod = new \ReflectionMethod($controller, $method);

        foreach ($reflectionMethod->getParameters() as $param) {
            // print_r(get_class_methods($param));
            if ($param->getClass()) {
                continue;
            }
            $optional = $param->isOptional() == TRUE ? '?' : "";
            $params .= '/{' . $param->getName() . $optional . '}';
        }
        return $params;
    }

}