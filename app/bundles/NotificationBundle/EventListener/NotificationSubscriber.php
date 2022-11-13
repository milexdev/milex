<?php

namespace Milex\NotificationBundle\EventListener;

use Milex\AssetBundle\Helper\TokenHelper as AssetTokenHelper;
use Milex\CoreBundle\Event\TokenReplacementEvent;
use Milex\CoreBundle\Model\AuditLogModel;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Helper\TokenHelper;
use Milex\NotificationBundle\Event\NotificationEvent;
use Milex\NotificationBundle\NotificationEvents;
use Milex\PageBundle\Entity\Trackable;
use Milex\PageBundle\Helper\TokenHelper as PageTokenHelper;
use Milex\PageBundle\Model\TrackableModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NotificationSubscriber implements EventSubscriberInterface
{
    /**
     * @var TrackableModel
     */
    private $trackableModel;

    /**
     * @var PageTokenHelper
     */
    private $pageTokenHelper;

    /**
     * @var AssetTokenHelper
     */
    private $assetTokenHelper;

    /**
     * @var AuditLogModel
     */
    private $auditLogModel;

    public function __construct(AuditLogModel $auditLogModel, TrackableModel $trackableModel, PageTokenHelper $pageTokenHelper, AssetTokenHelper $assetTokenHelper)
    {
        $this->auditLogModel    = $auditLogModel;
        $this->trackableModel   = $trackableModel;
        $this->pageTokenHelper  = $pageTokenHelper;
        $this->assetTokenHelper = $assetTokenHelper;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            NotificationEvents::NOTIFICATION_POST_SAVE   => ['onPostSave', 0],
            NotificationEvents::NOTIFICATION_POST_DELETE => ['onDelete', 0],
            NotificationEvents::TOKEN_REPLACEMENT        => ['onTokenReplacement', 0],
        ];
    }

    /**
     * Add an entry to the audit log.
     */
    public function onPostSave(NotificationEvent $event)
    {
        $entity = $event->getNotification();
        if ($details = $event->getChanges()) {
            $log = [
                'bundle'   => 'notification',
                'object'   => 'notification',
                'objectId' => $entity->getId(),
                'action'   => ($event->isNew()) ? 'create' : 'update',
                'details'  => $details,
            ];
            $this->auditLogModel->writeToLog($log);
        }
    }

    /**
     * Add a delete entry to the audit log.
     */
    public function onDelete(NotificationEvent $event)
    {
        $entity = $event->getNotification();
        $log    = [
            'bundle'   => 'notification',
            'object'   => 'notification',
            'objectId' => $entity->deletedId,
            'action'   => 'delete',
            'details'  => ['name' => $entity->getName()],
        ];
        $this->auditLogModel->writeToLog($log);
    }

    public function onTokenReplacement(TokenReplacementEvent $event)
    {
        /** @var Lead $lead */
        $lead         = $event->getLead();
        $content      = $event->getContent();
        $clickthrough = $event->getClickthrough();

        if ($content) {
            $tokens = array_merge(
                TokenHelper::findLeadTokens($content, $lead->getProfileFields()),
                $this->pageTokenHelper->findPageTokens($content, $clickthrough),
                $this->assetTokenHelper->findAssetTokens($content, $clickthrough)
            );

            list($content, $trackables) = $this->trackableModel->parseContentForTrackables(
                $content,
                $tokens,
                'notification',
                $clickthrough['channel'][1]
            );

            /**
             * @var string
             * @var Trackable $trackable
             */
            foreach ($trackables as $token => $trackable) {
                $tokens[$token] = $this->trackableModel->generateTrackableUrl($trackable, $clickthrough);
            }

            $content = str_replace(array_keys($tokens), array_values($tokens), $content);

            $event->setContent($content);
        }
    }
}
