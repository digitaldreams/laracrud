<?php

namespace LaraCrud\Crud;

use LaraCrud\Contracts\Crud;
use LaraCrud\Helpers\Helper;
use LaraCrud\Helpers\TemplateManager;
use LaraCrud\Repositories\TestRepository;

/**
 * Create Routes based on controller method and its parameters
 * We will use ReflectionClass to inspect Controller and its method to generate routes based on it.
 *
 * @author Tuhin
 */
class Test implements Crud
{
    use Helper;

    /**
     * @var
     */
    protected $suffix;

    /**
     * @var
     */
    protected $namespace;

    /**
     * @var string
     */
    protected string $fileName;

    protected TestRepository $testRepository;

    /**
     * Test constructor.
     *
     * @param \LaraCrud\Repositories\TestRepository $testRepository
     * @param                                       $fileName
     */
    public function __construct(TestRepository $testRepository, $fileName)
    {
        $this->namespace = config('laracrud.test.feature.namespace', 'Tests\Feature');
        $this->fileName = $fileName;
        $this->testRepository = $testRepository;
    }

    /**
     * Process template and return complete code.
     *
     * @return mixed
     */
    public function template()
    {
        $this->testRepository->build();
        $fileNames = explode("\\", $this->fileName);

        return (new TemplateManager('test/template.txt', [
            'namespace' => $this->namespace,
            'importNameSpace' => $this->makeNamespaceImportString(),
            'className' => array_pop($fileNames),
            'methods' => implode("\n", $this->testRepository->getCode()),
        ]))->get();
    }

    /**
     * Get code and save to disk.
     *
     * @return mixed
     * @throws \Exception
     *
     */
    public function save()
    {
        $fullPath = $this->toPath($this->namespace . '\\' . $this->fileName) . '.php';
        if (file_exists($fullPath)) {
            throw new \Exception('TestClass already exists');
        }
        $testClass = new \SplFileObject($fullPath, 'w+');
        $testClass->fwrite($this->template());
    }


    public function makeNamespaceImportString()
    {
        $ns = '';
        foreach ($this->testRepository->getImportableNamespaces() as $namespace) {
            $ns .= "\n use " . $namespace . ';';
        }

        return $ns;
    }
}
