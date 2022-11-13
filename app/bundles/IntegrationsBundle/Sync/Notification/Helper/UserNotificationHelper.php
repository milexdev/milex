<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Sync\Notification\Helper;

use Milex\IntegrationsBundle\Sync\Notification\Writer;
use Symfony\Component\Translation\TranslatorInterface;

class UserNotificationHelper
{
    /**
     * @var Writer
     */
    private $writer;

    /**
     * @var UserHelper
     */
    private $userHelper;

    /**
     * @var OwnerProvider
     */
    private $ownerProvider;

    /**
     * @var RouteHelper
     */
    private $routeHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $integrationDisplayName;

    /**
     * @var string
     */
    private $objectDisplayName;

    public function __construct(
        Writer $writer,
        UserHelper $userHelper,
        OwnerProvider $ownerProvider,
        RouteHelper $routeHelper,
        TranslatorInterface $translator
    ) {
        $this->writer        = $writer;
        $this->userHelper    = $userHelper;
        $this->ownerProvider = $ownerProvider;
        $this->routeHelper   = $routeHelper;
        $this->translator    = $translator;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Milex\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException
     */
    public function writeNotification(
        string $message,
        string $integrationDisplayName,
        string $objectDisplayName,
        string $milexObject,
        int $id,
        string $linkText
    ): void {
        $this->integrationDisplayName = $integrationDisplayName;
        $this->objectDisplayName      = $objectDisplayName;
        $link                         = $this->routeHelper->getLink($milexObject, $id, $linkText);
        $owners                       = $this->ownerProvider->getOwnersForObjectIds($milexObject, [$id]);

        if (!empty($owners[0]['owner_id'])) {
            $this->writeMessage($message, $link, (int) $owners[0]['owner_id']);

            return;
        }

        $adminUsers = $this->userHelper->getAdminUsers();
        foreach ($adminUsers as $userId) {
            $this->writeMessage($message, $link, $userId);
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    private function writeMessage(string $message, string $link, int $userId): void
    {
        $this->writer->writeUserNotification(
            $this->translator->trans(
                'milex.integration.sync.user_notification.header',
                [
                    '%integration%' => $this->integrationDisplayName,
                    '%object%'      => $this->objectDisplayName,
                ]
            ),
            $this->translator->trans(
                'milex.integration.sync.user_notification.sync_error',
                [
                    '%name%'    => $link,
                    '%message%' => $message,
                ]
            ),
            $userId
        );
    }
}
