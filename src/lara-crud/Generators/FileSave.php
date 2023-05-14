<?php

namespace LaraCrud\Generators;

use LaraCrud\Helpers\NamespaceResolver;
use LaraCrud\Services\Mapper;

trait FileSave
{
    public function save(): void
    {
        $filePath = NamespaceResolver::checkPath($this->getNamespace(), $this->getClassName());
        $file = new \SplFileObject($filePath, 'w+');
        $file->fwrite($this->template());
        $file->fflush();
        $this->triggerEvent();
    }

    protected function triggerEvent(): void
    {
        switch (get_class($this)) {
            case ApiResource::class:
                Mapper::loadByModel($this->model, [
                    'apiResource' => $this->getClassName(),
                    'apiResourceNamespace' => $this->getFullNamespace(),
                ])->save();
                break;
            case Model::class:
                Mapper::loadByTable($this->table->name(), [
                    'model' => $this->getClassName(),
                    'modelNamespace' => $this->getFullNamespace(),
                ])->save();
                break;
            case Controller::class:
                Mapper::loadByModel($this->model, [
                    'controller' => $this->getClassName(),
                    'controllerNamespace' => $this->getFullNamespace(),
                ])->save();
                break;
            case Policy::class:
                Mapper::loadByModel($this->modelFullClass, [
                    'policy' => $this->getClassName(),
                    'policyNamespace' => $this->getFullNamespace(),
                ])->save();
                break;
        }
    }

    public function getFullNamespace(): string
    {
        return $this->getNamespace() . '\\' . $this->getClassName();
    }
}
