<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\Notification;

use Milex\IntegrationsBundle\Exception\IntegrationNotFoundException;
use Milex\IntegrationsBundle\Helper\ConfigIntegrationsHelper;
use Milex\IntegrationsBundle\Helper\SyncIntegrationsHelper;
use Milex\IntegrationsBundle\Integration\Interfaces\ConfigFormSyncInterface;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\NotificationDAO;
use Milex\IntegrationsBundle\Sync\Exception\HandlerNotSupportedException;
use Milex\IntegrationsBundle\Sync\Notification\Handler\HandlerContainer;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\MilexSyncDataExchange;

class Notifier
{
    /**
     * @var HandlerContainer
     */
    private $handlerContainer;

    /**
     * @var SyncIntegrationsHelper
     */
    private $syncIntegrationsHelper;

    /**
     * @var ConfigIntegrationsHelper
     */
    private $configIntegrationsHelper;

    public function __construct(
        HandlerContainer $handlerContainer,
        SyncIntegrationsHelper $syncIntegrationsHelper,
        ConfigIntegrationsHelper $configIntegrationsHelper
    ) {
        $this->handlerContainer         = $handlerContainer;
        $this->syncIntegrationsHelper   = $syncIntegrationsHelper;
        $this->configIntegrationsHelper = $configIntegrationsHelper;
    }

    /**
     * @param NotificationDAO[] $notifications
     * @param string            $integrationHandler
     *
     * @throws HandlerNotSupportedException
     * @throws IntegrationNotFoundException
     */
    public function noteMilexSyncIssue(array $notifications, $integrationHandler = MilexSyncDataExchange::NAME): void
    {
        foreach ($notifications as $notification) {
            $handler = $this->handlerContainer->getHandler($integrationHandler, $notification->getMilexObject());

            $integrationDisplayName = $this->syncIntegrationsHelper->getIntegration($notification->getIntegration())->getDisplayName();
            $objectDisplayName      = $this->getObjectDisplayName($notification->getIntegration(), $notification->getIntegrationObject());

            $handler->writeEntry($notification, $integrationDisplayName, $objectDisplayName);
        }
    }

    /**
     * Finalizes notifications such as pushing summary entries to the user notifications.
     */
    public function finalizeNotifications(): void
    {
        foreach ($this->handlerContainer->getHandlers() as $handler) {
            $handler->finalize();
        }
    }

    /**
     * @return string
     */
    private function getObjectDisplayName(string $integration, string $object)
    {
        try {
            $configIntegration = $this->configIntegrationsHelper->getIntegration($integration);
        } catch (IntegrationNotFoundException $exception) {
            return ucfirst($object);
        }

        if (!$configIntegration instanceof ConfigFormSyncInterface) {
            return ucfirst($object);
        }

        $objects = $configIntegration->getSyncConfigObjects();

        if (!isset($objects[$object])) {
            return ucfirst($object);
        }

        return $objects[$object];
    }
}
