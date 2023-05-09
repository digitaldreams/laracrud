<?php

namespace LaraCrud\Builder\Controller\Api;

use LaraCrud\Builder\Controller\DestroyMethod as ParentDestroyMethod;
use LaraCrud\Contracts\Controller\ApiArrayResponseMethod;

class DestroyMethod extends ParentDestroyMethod implements ApiArrayResponseMethod
{
    public function array(): array
    {
        return [
            'success' => true,
            'message' => sprintf('%s deleted successfully', $this->getModelShortName()),
        ];
    }
}
