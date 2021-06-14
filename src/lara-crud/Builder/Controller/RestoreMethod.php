<?php

namespace LaraCrud\Builder\Controller;

abstract class RestoreMethod extends ControllerMethod
{
    /**
     * {@inheritdoc}
     */
    protected function beforeGenerate(): self
    {
        if ($this->parentModel) {
            $this->setParameter($this->getParentShortName(), '$' . $this->getParentVariableName());
        }
        $this->setParameter('int', '$' . $this->getModelVariableName());

        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        $variable = '$' . $this->getModelVariableName();
        $body = $variable . ' = ' . $this->getModelShortName() . '::withTrashed()->where(\'' . $this->model->getRouteKeyName() . '\',' . $variable . ')->firstOrFail();' . PHP_EOL;

        $body .= "\t\t" . $variable . '->restore();' . PHP_EOL;

        return $body;
    }

    public function phpDocComment(): string
    {
        return sprintf('Restore a previously deleted %s .', $this->getModelShortName());
    }
}
