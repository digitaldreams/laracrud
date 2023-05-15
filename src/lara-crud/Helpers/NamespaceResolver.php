<?php

namespace LaraCrud\Helpers;

class NamespaceResolver
{
    public static function getFullNS(string $namespace)
    {
        $rootNs = config('laracrud.rootNamespace', 'App');
        if (empty($namespace)) {
            return $rootNs;
        }
        if (0 !== substr_compare((string)$namespace, (string)$rootNs, 0, strlen((string)$rootNs))) {
            return trim((string)$rootNs, '\\') . '\\' . $namespace;
        }

        return $namespace;
    }

    /**
     * @param string $namespace Full Qualified namespace e.g. App\Http\Controllers
     *
     * @return string will be return like app/Http/Controllers
     */
    public static function toPath(string $namespace)
    {
        $nsArr = explode('\\', trim($namespace, '\\'));
        $rootNs = array_shift($nsArr);
        $loadComposerJson = new \SplFileObject(base_path('composer.json'));
        $composerArr = json_decode(
            $loadComposerJson->fread($loadComposerJson->getSize()),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $psr4 = $composerArr['autoload']['psr-4'] ?? [];
        $rootPath = $psr4[$rootNs . '\\'] ?? lcfirst($rootNs);

        return rtrim((string)$rootPath, '/') . '/' . implode('/', $nsArr);
    }

    public static function modelFullName(string $model): false|string
    {
        $modelNamespace = static::getFullNS(config('laracrud.model.namespace', 'App'));
        if (!class_exists($model)) {
            return $modelNamespace . '\\' . $model;
        }

        return false;
    }

    public static function getResourceRoot(): string
    {
        return static::getFullNS(config('laracrud.resource.namespace', 'App\Http\Resources'));
    }

    public static function getRequestRoot(bool $isApi = false): string
    {
        $ns = !empty($isApi) ? config('laracrud.request.apiNamespace') : config('laracrud.request.namespace');

        return static::getFullNS($ns);
    }

    public static function getControllerRoot(bool $isApi = false): string
    {
        return $isApi === true ? config('laracrud.controller.apiNamespace', 'App\Http\Controllers\Api') : config(
            'laracrud.controller.namespace',
            'App\Http\Controllers'
        );
    }

    /**
     * Convert NS to path and then check if it exists if not then create it. Then return full specified path of the
     * class.
     *
     * @param string $extension
     *
     * @return mixed
     */
    public static function checkPath(string $namespace, string $name, string $extension = '.php')
    {
        $fullPath = base_path(static::toPath($namespace));
        if (!file_exists($fullPath)) {
            $relPath = static::toPath($namespace);
            $nextPath = '';
            $folders = explode('/', (string)$relPath);
            foreach ($folders as $folder) {
                $nextPath .= !empty($nextPath) ? '/' . $folder : $folder;
                if (!file_exists(base_path($nextPath))) {
                    mkdir(base_path($nextPath), 0755);
                }
            }
        }

        return base_path(static::toPath($namespace) . '/' . $name . $extension);
    }

}
