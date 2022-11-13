<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\SyncDataExchange;

use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\OrderDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Request\RequestDAO;

interface SyncDataExchangeInterface
{
    /**
     * Sync to integration.
     */
    public function getSyncReport(RequestDAO $requestDAO): ReportDAO;

    /**
     * Sync from integration.
     */
    public function executeSyncOrder(OrderDAO $syncOrderDAO);
}
