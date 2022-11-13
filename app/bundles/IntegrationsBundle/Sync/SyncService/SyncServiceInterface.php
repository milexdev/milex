<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\SyncService;

use Milex\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;

interface SyncServiceInterface
{
    public function processIntegrationSync(InputOptionsDAO $inputOptionsDAO);
}
