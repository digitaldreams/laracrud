<?php

namespace LaraCrud\Builder\Controller\Api;

use LaraCrud\Builder\Controller\ForceDeleteMethod as ParentForceDeleteMethod;
use LaraCrud\Contracts\Controller\ApiArrayResponseMethod;

class ForceDeleteMethod extends ParentForceDeleteMethod implements ApiArrayResponseMethod
{
    public function array(): array
    {
        return [
            'success' => true,
            'message' => sprintf('%s removed from the bin permanently.', $this->getModelShortName()),
        ];
    }
}
