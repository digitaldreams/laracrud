<?php

namespace LaraCrud\Helpers;

use Illuminate\Support\Str;

trait RedirectAbleMethodHelper
{
    /**
     * Route name. E.g. blog::posts.index .
     *
     * @var string
     */
    protected string $routeName;

    /**
     * Route parameter.
     *
     * @var array
     */
    protected array $routeParameter = [];

    /**
     * Session flash message key after successful operation of model.
     *
     * @var string
     */
    protected string $flashKeyName = 'message';

    /**
     *  Session flash message after successful operation of model.
     *
     * @var string
     */
    protected string $flashMessage = '';

    public function getFlashMessage(): string
    {
        return $this->getModelShortName() . ' successfully ' . $this->getMethodName();
    }

    public function getFlashMessageKey(): string
    {
        return $this->flashKeyName;
    }

    protected function generateRedirectAbleCode(): string
    {
        return (new TemplateManager('controller/web/save.txt', [
            'parameters' => $this->buildParameters(),
            'body' => $this->getBody(),
            'methodName' => $this->getMethodName(),
            'route' => $this->route(),
            'flashKey' => $this->getFlashMessageKey(),
            'flashMessage' => $this->getFlashMessage(),
            'PHPDocComment' => $this->phpDocComment(),
            'authorization' => $this->getAuthorization(),
            'modelVariable' => $this->getModelVariableName(),
            'model' => $this->getModelShortName(),
            'parentModelVariable' => $this->getParentVariableName(),
            'parentModel' => $this->getParentShortName(),
        ]))->get();
    }

    public function route(): string
    {
        $routeName = $this->generateRouteName();
        $routeParameters = $this->generateRouteParameter();

        if (empty($routeParameters)) {
            return $routeName;
        }

        if (1 === count($routeParameters)) {
            return $routeName . ',' . array_shift($routeParameters);
        }
        $paramString = '[';
        foreach ($routeParameters as $key => $variable) {
            $paramString .= "'" . $key . "' => " . $variable . ',';
        }

        return $routeName . ',' . $paramString . ']';
    }

    /**
     * @return string
     */
    protected function generateRouteName()
    {
        $name = config('laracrud.route.prefix') ? rtrim((string) config('laracrud.route.prefix'), '::') . '::' : '';
        if ($this->parentModel) {
            $name .= $this->toRouteString($this->getParentVariableName()) . '.';
        }
        $name .= $this->toRouteString($this->getModelVariableName()) . '.' . $this->redirectToRouteMethodName();

        return "'" . $name . "'";
    }

    /**
     * After completing an action in which method application will redirect to e.g. show.
     *
     * @return mixed
     */
    public function redirectToRouteMethodName(): string
    {
        return 'show';
    }

    /**
     * @param $name
     */
    protected function toRouteString($name): string
    {
        return Str::plural($name);
    }

    /**
     * @return array
     */
    protected function generateRouteParameter()
    {
        if ($this->parentModel) {
            $this->routeParameter[$this->getParentVariableName()] = '$' . $this->getParentVariableName() . '->' . $this->parentModel->getRouteKeyName();
        }
        $this->routeParameter[$this->getModelVariableName()] = '$' . $this->getModelVariableName() . '->' . $this->model->getRouteKeyName();

        return $this->routeParameter;
    }

    /**
     * @param $key
     * @param $variable
     *
     * @return \LaraCrud\Helpers\RedirectAbleMethodHelper
     */
    public function setRouteParameter(string $key, string $variable)
    {
        $this->routeParameter[$key] = $variable;

        return $this;
    }
}
