<?php

namespace LaraCrud\Builder\Test\Methods;

use Illuminate\Support\Str;
use LaraCrud\Services\ControllerMethodReader;

abstract class ControllerMethod extends ControllerMethodReader
{
    protected array $testMethods = [];

    /**
     * Whether its an API method or not.
     *
     */
    protected bool $isApi = false;

    protected string $modelFactory;

    protected string $parentModelFactory;

    public static bool $hasSuperAdminRole = false;

    protected array $fake = [];

    protected array $endFake = [];


    abstract public function before(): void;

    /**
     * Get Inside code of a Controller Method.
     *
     * @throws \ReflectionException
     */
    public function getCode(): string
    {
        $this->parseRoute();
        $this->setFakeStorage()->before();

        return implode("\n", $this->testMethods);
    }

    protected function getModelFactory(): string
    {
        return $this->modelFactory;
    }

    protected function getParentModelFactory(): string
    {
        return $this->parentModelFactory;
    }

    /**
     * Whether Current route need Auth.
     */
    protected function isAuthRequired(): bool
    {
        $auth = array_intersect($this->authMiddleware, $this->route->gatherMiddleware());

        if (count($auth) > 0) {
            if (in_array('auth', $auth)) {
                $this->isWebAuth = true;
            }
            if (in_array('auth:sanctum', $auth)) {
                $this->isSanctumAuth = true;
            }
            if (in_array('auth:api', $auth)) {
                $this->isPassportAuth = true;
            }

            return true;
        }

        return false;
    }

    /**
     * @return false|string
     */
    protected function getSanctumActingAs(string $actionAs)
    {
        if (!$this->isSanctumAuth) {
            return false;
        }
        $this->namespaces[] = \Laravel\Sanctum\Sanctum::class;

        return 'Sanctum::actingAs(' . $actionAs . ', [\'*\']);';
    }

    /**
     * @return false|string
     */
    protected function getPassportActingAs(string $actionAs)
    {
        if (!$this->isPassportAuth) {
            return false;
        }

        $this->namespaces[] = 'Laravel\Passport\Passport';

        return 'Passport::actingAs(' . $actionAs . ', [\'*\']);';
    }

    protected function getWebAuthActingAs(string $actionAs): false|string
    {
        if (!$this->isWebAuth) {
            return false;
        }

        return 'actingAs(' . $actionAs . ')->';
    }

    /**
     * Whether current application has Super Admin Role.
     */
    protected function hasSuperAdminRole(): bool
    {
        return static::$hasSuperAdminRole;
    }


    protected function getApiActingAs(string $actionAs): false|string
    {
        if ($this->isSanctumAuth) {
            return $this->getSanctumActingAs($actionAs);
        }
        if ($this->isPassportAuth) {
            return $this->getPassportActingAs($actionAs);
        }

        return '';
    }

    protected function getGlobalVariables(string $actionAs = '$user'): array
    {
        return [
            'modelVariable' => $this->getModelVariable(),
            'modelShortName' => $this->modelRelationReader->getShortName(),
            'route' => $this->getRoute(),
            'modelMethodName' => Str::snake($this->modelRelationReader->getShortName()),
            'apiActingAs' => $this->getApiActingAs($actionAs),
            'webActingAs' => $this->isWebAuth ? $this->getWebAuthActingAs($actionAs) : '',
            'table' => $this->model->getTable(),
            'assertDeleted' => $this->modelRelationReader->isSoftDeleteAble() ? 'assertSoftDeleted' : 'assertDeleted',
            'fake' => implode("\n", array_unique($this->fake)),
            'endFake' => implode("\n", array_unique($this->endFake)),
            'parentVariable' => $this->parentVariable,

        ];
    }

    public function generatePostData(bool $update = false): string
    {
        $data = '';
        $modelVariable = $update === true ? '$new' . $this->modelRelationReader->getShortName() : $this->getModelVariable();
        $rules = $this->getCustomRequestClassRules();
        foreach ($rules as $field => $rule) {
            $data .= "\t\t\t" . '"' . $field . '" => ' . $modelVariable . '->' . $field . ',' . PHP_EOL;
        }

        return $data;
    }

    public function generateDataProvider(): string
    {
        $data = '';
        $rules = $this->getCustomRequestClassRules();
        foreach ($rules as $field => $rule) {
            $listOfRules = is_array($rule) ? $rule : explode("|", (string)$rule);
            foreach ($listOfRules as $listOfRule) {
                if (is_object($listOfRule)) {
                    continue;
                }
                if (in_array($listOfRule, static::$ignoreDataProviderRules)) {
                    continue;
                }
                $data .= "\t\t\t" . '"' . "The $field must be $listOfRule" . '"' . ' => ["' . $field . '"," " ],' . PHP_EOL;
            }
        }

        return $data;
    }

    protected function setFakeStorage(): self
    {
        if ($this->hasFile()) {
            $this->namespaces[] = \Illuminate\Support\Facades\Storage::class;
            $this->namespaces[] = \Illuminate\Http\UploadedFile::class;
            $this->fake[] = 'Storage::fake(\'file\');';
            $this->fake[] = '$file = UploadedFile::fake()->create(\'poster.jpg\');';

            $this->endFake[] = 'Storage::disk(\'file\')->assertExists(\'photo1.jpg\');';
        }
        return $this;
    }
}
