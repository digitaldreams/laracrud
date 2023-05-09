<?php

namespace LaraCrud\Builder\Controller\Web;

/**
 * Edit method is same as ShowMethod Class.
 */
class EditMethod extends ShowMethod
{
    public function phpDocComment(): string
    {
        return sprintf('Show the form for editing the specified %s.', $this->getModelShortName());
    }
}
