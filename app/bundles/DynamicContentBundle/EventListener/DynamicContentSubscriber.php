<?php

namespace Milex\DynamicContentBundle\EventListener;

use DOMDocument;
use DOMXPath;
use Milex\AssetBundle\Helper\TokenHelper as AssetTokenHelper;
use Milex\CoreBundle\Event as MilexEvents;
use Milex\CoreBundle\Model\AuditLogModel;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\DynamicContentBundle\DynamicContentEvents;
use Milex\DynamicContentBundle\Entity\DynamicContent;
use Milex\DynamicContentBundle\Event as Events;
use Milex\DynamicContentBundle\Helper\DynamicContentHelper;
use Milex\DynamicContentBundle\Model\DynamicContentModel;
use Milex\EmailBundle\EventListener\MatchFilterForLeadTrait;
use Milex\FormBundle\Helper\TokenHelper as FormTokenHelper;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Helper\TokenHelper;
use Milex\LeadBundle\Tracker\ContactTracker;
use Milex\PageBundle\Entity\Trackable;
use Milex\PageBundle\Event\PageDisplayEvent;
use Milex\PageBundle\Helper\TokenHelper as PageTokenHelper;
use Milex\PageBundle\Model\TrackableModel;
use Milex\PageBundle\PageEvents;
use MilexPlugin\MilexFocusBundle\Helper\TokenHelper as FocusTokenHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DynamicContentSubscriber implements EventSubscriberInterface
{
    use MatchFilterForLeadTrait;

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
     * @var FormTokenHelper
     */
    private $formTokenHelper;

    /**
     * @var FocusTokenHelper
     */
    private $focusTokenHelper;

    /**
     * @var AuditLogModel
     */
    private $auditLogModel;

    /**
     * @var DynamicContentHelper
     */
    private $dynamicContentHelper;

    /**
     * @var DynamicContentModel
     */
    private $dynamicContentModel;

    /**
     * @var CorePermissions
     */
    private $security;

    /**
     * @var ContactTracker
     */
    private $contactTracker;

    public function __construct(
        TrackableModel $trackableModel,
        PageTokenHelper $pageTokenHelper,
        AssetTokenHelper $assetTokenHelper,
        FormTokenHelper $formTokenHelper,
        FocusTokenHelper $focusTokenHelper,
        AuditLogModel $auditLogModel,
        DynamicContentHelper $dynamicContentHelper,
        DynamicContentModel $dynamicContentModel,
        CorePermissions $security,
        ContactTracker $contactTracker
    ) {
        $this->trackableModel       = $trackableModel;
        $this->pageTokenHelper      = $pageTokenHelper;
        $this->assetTokenHelper     = $assetTokenHelper;
        $this->formTokenHelper      = $formTokenHelper;
        $this->focusTokenHelper     = $focusTokenHelper;
        $this->auditLogModel        = $auditLogModel;
        $this->dynamicContentHelper = $dynamicContentHelper;
        $this->dynamicContentModel  = $dynamicContentModel;
        $this->security             = $security;
        $this->contactTracker       = $contactTracker;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            DynamicContentEvents::POST_SAVE         => ['onPostSave', 0],
            DynamicContentEvents::POST_DELETE       => ['onDelete', 0],
            DynamicContentEvents::TOKEN_REPLACEMENT => ['onTokenReplacement', 0],
            PageEvents::PAGE_ON_DISPLAY             => ['decodeTokens', 254],
        ];
    }

    /**
     * Add an entry to the audit log.
     */
    public function onPostSave(Events\DynamicContentEvent $event)
    {
        $entity = $event->getDynamicContent();
        if ($details = $event->getChanges()) {
            $log = [
                'bundle'   => 'dynamicContent',
                'object'   => 'dynamicContent',
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
    public function onDelete(Events\DynamicContentEvent $event)
    {
        $entity = $event->getDynamicContent();
        $log    = [
            'bundle'   => 'dynamicContent',
            'object'   => 'dynamicContent',
            'objectId' => $entity->deletedId,
            'action'   => 'delete',
            'details'  => ['name' => $entity->getName()],
        ];
        $this->auditLogModel->writeToLog($log);
    }

    public function onTokenReplacement(MilexEvents\TokenReplacementEvent $event)
    {
        /** @var Lead $lead */
        $lead         = $event->getLead();
        $content      = $event->getContent();
        $clickthrough = $event->getClickthrough();

        if ($content) {
            $tokens = array_merge(
                TokenHelper::findLeadTokens($content, $lead->getProfileFields()),
                $this->pageTokenHelper->findPageTokens($content, $clickthrough),
                $this->assetTokenHelper->findAssetTokens($content, $clickthrough),
                $this->formTokenHelper->findFormTokens($content),
                $this->focusTokenHelper->findFocusTokens($content)
            );

            list($content, $trackables) = $this->trackableModel->parseContentForTrackables(
                $content,
                $tokens,
                'dynamicContent',
                $clickthrough['dynamic_content_id']
            );

            $dwc     =  $this->dynamicContentModel->getEntity($clickthrough['dynamic_content_id']);
            $utmTags = [];
            if ($dwc && $dwc instanceof DynamicContent) {
                $utmTags = $dwc->getUtmTags();
            }

            /**
             * @var string
             * @var Trackable $trackable
             */
            foreach ($trackables as $token => $trackable) {
                $tokens[$token] = $this->trackableModel->generateTrackableUrl($trackable, $clickthrough, false, $utmTags);
            }

            $content = str_replace(array_keys($tokens), array_values($tokens), $content);

            $event->setContent($content);
        }
    }

    public function decodeTokens(PageDisplayEvent $event)
    {
        $lead = $this->security->isAnonymous() ? $this->contactTracker->getContact() : null;
        if (!$lead) {
            return;
        }

        $content = $event->getContent();
        if (empty($content)) {
            return;
        }

        $tokens    = $this->dynamicContentHelper->findDwcTokens($content, $lead);
        $leadArray = [];
        if ($lead instanceof Lead) {
            $leadArray = $this->dynamicContentHelper->convertLeadToArray($lead);
        }
        $result = [];
        foreach ($tokens as $token => $dwc) {
            $result[$token] = '';
            if ($this->matchFilterForLead($dwc['filters'], $leadArray)) {
                $result[$token] = $dwc['content'];
            }
        }
        $content = str_replace(array_keys($result), array_values($result), $content);

        // replace slots
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR);
        $xpath = new DOMXPath($dom);

        $divContent = $xpath->query('//*[@data-slot="dwc"]');
        for ($i = 0; $i < $divContent->length; ++$i) {
            $slot = $divContent->item($i);
            if (!$slotName = $slot->getAttribute('data-param-slot-name')) {
                continue;
            }

            if (!$slotContent = $this->dynamicContentHelper->getDynamicContentForLead($slotName, $lead)) {
                continue;
            }

            $newnode = $dom->createDocumentFragment();
            $newnode->appendXML('<![CDATA['.mb_convert_encoding($slotContent, 'HTML-ENTITIES', 'UTF-8').']]>');
            $slot->parentNode->replaceChild($newnode, $slot);
        }

        $content = $dom->saveHTML();

        $event->setContent($content);
    }
}
