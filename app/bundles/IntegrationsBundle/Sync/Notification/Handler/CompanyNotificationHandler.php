<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\Notification\Handler;

use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\NotificationDAO;
use Milex\IntegrationsBundle\Sync\Notification\Helper\CompanyHelper;
use Milex\IntegrationsBundle\Sync\Notification\Helper\UserNotificationHelper;
use Milex\IntegrationsBundle\Sync\Notification\Writer;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\MilexSyncDataExchange;

class CompanyNotificationHandler implements HandlerInterface
{
    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var UserNotificationHelper
     */
    private $userNotificationHelper;

    /**
     * @var CompanyHelper
     */
    private $companyHelper;

    public function __construct(Writer $writer, UserNotificationHelper $userNotificationHelper, CompanyHelper $companyHelper)
    {
        $this->writer                 = $writer;
        $this->userNotificationHelper = $userNotificationHelper;
        $this->companyHelper          = $companyHelper;
    }

    public function getIntegration(): string
    {
        return MilexSyncDataExchange::NAME;
    }

    public function getSupportedObject(): string
    {
        return MilexSyncDataExchange::OBJECT_COMPANY;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Milex\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException
     */
    public function writeEntry(NotificationDAO $notificationDAO, string $integrationDisplayName, string $objectDisplayName): void
    {
        $this->writer->writeAuditLogEntry(
            $notificationDAO->getIntegration(),
            $notificationDAO->getMilexObject(),
            $notificationDAO->getMilexObjectId(),
            'sync',
            [
                'integrationObject'   => $notificationDAO->getIntegrationObject(),
                'integrationObjectId' => $notificationDAO->getIntegrationObjectId(),
                'message'             => $notificationDAO->getMessage(),
            ]
        );

        $this->userNotificationHelper->writeNotification(
            $notificationDAO->getMessage(),
            $integrationDisplayName,
            $objectDisplayName,
            $notificationDAO->getMilexObject(),
            $notificationDAO->getMilexObjectId(),
            (string) $this->companyHelper->getCompanyName($notificationDAO->getMilexObjectId())
        );
    }

    public function finalize(): void
    {
        // Nothing to do
    }
}
