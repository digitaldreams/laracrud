<?php

namespace LaraCrud\Helpers;

class NamespaceResolver
{
    public  static  function getFullNS(string $namespace)
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
        $composerArr = json_decode($loadComposerJson->fread($loadComposerJson->getSize()), true, 512, JSON_THROW_ON_ERROR);
        $psr4 = $composerArr['autoload']['psr-4'] ?? [];
        $rootPath = $psr4[$rootNs . '\\'] ?? lcfirst($rootNs);

        return rtrim((string) $rootPath, '/') . '/' . implode('/', $nsArr);
    }

    public static function modelFullName(string $model): false|string
    {
        $modelNamespace = static::getFullNS(config('laracrud.model.namespace', 'App'));
        if (!class_exists($model)) {
            return $modelNamespace . '\\' . $model;
        }

        return false;
    }
}
