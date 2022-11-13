<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\ValueNormalizer;

use Milex\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;

interface ValueNormalizerInterface
{
    /**
     * @param $value
     * @param $type
     */
    public function normalizeForMilex(string $value, $type): NormalizedValueDAO;

    /**
     * @return mixed
     */
    public function normalizeForIntegration(NormalizedValueDAO $value);
}
