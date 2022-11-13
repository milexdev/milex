<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\VariableExpresser;

use Milex\IntegrationsBundle\Sync\DAO\Value\EncodedValueDAO;
use Milex\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;

interface VariableExpresserHelperInterface
{
    public function decodeVariable(EncodedValueDAO $EncodedValueDAO): NormalizedValueDAO;

    /**
     * @param mixed $var
     */
    public function encodeVariable($var): EncodedValueDAO;
}
