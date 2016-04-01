<?php

namespace App\Libs;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContainerCrud
 *
 * @author Tuhin
 */
class ControllerCrud extends LaraCrud {

    protected $controllerName;
    protected $modelName;
    protected $viewPath;

    public function __construct($modelName = '') {
        $this->modelName = $modelName;
        $this->init();
    }

    public function init() {
        if (!empty($this->modelName)) {
            $arr = explode('\\', $this->modelName);
            if (count($arr)) {
                $this->controllerName = array_pop($arr);
                $this->viewPath = strtolower($this->controllerName);
            }
        }
    }

    public function generateContent() {
        $contents = '';

        $contents = $this->getTempFile('controller.txt');
        $contents = str_replace("@@controllerName@@", $this->controllerName, $contents);
        $contents = str_replace("@@modelName@@", $this->modelName, $contents);
        $contents = str_replace("@@viewPath@@", $this->viewPath, $contents);

        return $contents;
    }

    public function make() {
        try {
            $controllerFileName = $this->controllerName . 'Controller.php';
            $fullPath = app_path('Http/Controllers/') . $controllerFileName;

            if (!file_exists($fullPath)) {
                $modelContent = $this->generateContent();
                $this->saveFile($fullPath, $modelContent);
                return true;
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage(), $ex->getCode(), $ex);
        }
        return false;
    }

}
