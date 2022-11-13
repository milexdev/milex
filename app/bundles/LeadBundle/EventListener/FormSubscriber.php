<?php

namespace Milex\LeadBundle\EventListener;

use Milex\CoreBundle\Helper\IpLookupHelper;
use Milex\EmailBundle\Model\EmailModel;
use Milex\FormBundle\Event\FormBuilderEvent;
use Milex\FormBundle\Event\SubmissionEvent;
use Milex\FormBundle\FormEvents;
use Milex\LeadBundle\Entity\PointsChangeLog;
use Milex\LeadBundle\Entity\UtmTag;
use Milex\LeadBundle\Form\Type\ActionAddUtmTagsType;
use Milex\LeadBundle\Form\Type\ActionRemoveDoNotContact;
use Milex\LeadBundle\Form\Type\CompanyChangeScoreActionType;
use Milex\LeadBundle\Form\Type\FormSubmitActionPointsChangeType;
use Milex\LeadBundle\Form\Type\ListActionType;
use Milex\LeadBundle\Form\Type\ModifyLeadTagsType;
use Milex\LeadBundle\Model\LeadModel;
use Milex\LeadBundle\Tracker\ContactTracker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FormSubscriber implements EventSubscriberInterface
{
    /**
     * @var EmailModel
     */
    private $emailModel;

    /**
     * @param LeadModel
     */
    protected $leadModel;

    /**
     * @var ContactTracker
     */
    protected $contactTracker;

    /**
     * @var IpLookupHelper
     */
    protected $ipLookupHelper;

    public function __construct(
        EmailModel $emailModel,
        LeadModel $leadModel,
        ContactTracker $contactTracker,
        IpLookupHelper $ipLookupHelper
    ) {
        $this->emailModel     = $emailModel;
        $this->leadModel      = $leadModel;
        $this->contactTracker = $contactTracker;
        $this->ipLookupHelper = $ipLookupHelper;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::FORM_ON_BUILD            => ['onFormBuilder', 0],
            FormEvents::ON_EXECUTE_SUBMIT_ACTION => [
                ['onFormSubmitActionChangePoints', 0],
                ['onFormSubmitActionChangeList', 1],
                ['onFormSubmitActionChangeTags', 2],
                ['onFormSubmitActionAddUtmTags', 3],
                ['onFormSubmitActionScoreContactsCompanies', 4],
                ['onFormSubmitActionRemoveFromDoNotContact', 5],
            ],
        ];
    }

    /**
     * Add a lead generation action to available form submit actions.
     */
    public function onFormBuilder(FormBuilderEvent $event)
    {
        $event->addSubmitAction('lead.pointschange', [
            'group'       => 'milex.lead.lead.submitaction',
            'label'       => 'milex.lead.lead.submitaction.changepoints',
            'description' => 'milex.lead.lead.submitaction.changepoints_descr',
            'formType'    => FormSubmitActionPointsChangeType::class,
            'formTheme'   => 'MilexLeadBundle:FormTheme\\FormActionChangePoints',
            'eventName'   => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
        ]);

        $event->addSubmitAction('lead.changelist', [
            'group'       => 'milex.lead.lead.submitaction',
            'label'       => 'milex.lead.lead.events.changelist',
            'description' => 'milex.lead.lead.events.changelist_descr',
            'formType'    => ListActionType::class,
            'eventName'   => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
        ]);

        $event->addSubmitAction('lead.changetags', [
            'group'             => 'milex.lead.lead.submitaction',
            'label'             => 'milex.lead.lead.events.changetags',
            'description'       => 'milex.lead.lead.events.changetags_descr',
            'formType'          => ModifyLeadTagsType::class,
            'eventName'         => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
        ]);

        $event->addSubmitAction('lead.addutmtags', [
            'group'             => 'milex.lead.lead.submitaction',
            'label'             => 'milex.lead.lead.events.addutmtags',
            'description'       => 'milex.lead.lead.events.addutmtags_descr',
            'formType'          => ActionAddUtmTagsType::class,
            'formTheme'         => 'MilexLeadBundle:FormTheme\\ActionAddUtmTags',
            'eventName'         => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
            'allowCampaignForm' => true,
        ]);

        $event->addSubmitAction('lead.remove_do_not_contact', [
            'group'             => 'milex.lead.lead.submitaction',
            'label'             => 'milex.lead.lead.events.removedonotcontact',
            'description'       => 'milex.lead.lead.events.removedonotcontact_descr',
            'formType'          => ActionRemoveDoNotContact::class,
            'formTheme'         => 'MilexLeadBundle:FormTheme\\ActionRemoveDoNotContact',
            'eventName'         => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
            'allowCampaignForm' => true,
        ]);

        $event->addSubmitAction('lead.scorecontactscompanies', [
            'group'       => 'milex.lead.lead.submitaction',
            'label'       => 'milex.lead.lead.events.changecompanyscore',
            'description' => 'milex.lead.lead.events.changecompanyscore_descr',
            'formType'    => CompanyChangeScoreActionType::class,
            'eventName'   => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
        ]);
    }

    public function onFormSubmitActionChangePoints(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('lead.pointschange')) {
            return;
        }

        if (!$contact = $this->contactTracker->getContact()) {
            return;
        }

        $form = $event->getSubmission()->getForm();

        $pointsChangeLog = new PointsChangeLog();
        $pointsChangeLog->setType('form');
        $pointsChangeLog->setEventName($form->getId().':'.$form->getName());
        $pointsChangeLog->setActionName($event->getAction()->getName());
        $pointsChangeLog->setIpAddress($this->ipLookupHelper->getIpAddress());
        $pointsChangeLog->setDateAdded(new \DateTime());
        $pointsChangeLog->setLead($contact);

        $oldPoints  = $contact->getPoints();
        $properties = $event->getActionConfig();

        $contact->adjustPoints($properties['points'], $properties['operator']);

        $newPoints = $contact->getPoints();

        $pointsChangeLog->setDelta($newPoints - $oldPoints);
        $contact->addPointsChangeLog($pointsChangeLog);

        $this->leadModel->saveEntity($contact, false);

        $event->getSubmission()->getLead()->setPoints($contact->getPoints());
    }

    public function onFormSubmitActionChangeList(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('lead.changelist')) {
            return;
        }

        if (!$contact = $this->contactTracker->getContact()) {
            return;
        }

        $properties = $event->getAction()->getProperties();
        $addTo      = $properties['addToLists'] ?? null;
        $removeFrom = $properties['removeFromLists'] ?? null;

        if (!empty($addTo)) {
            $this->leadModel->addToLists($contact, $addTo);
        }

        if (!empty($removeFrom)) {
            $this->leadModel->removeFromLists($contact, $removeFrom);
        }
    }

    public function onFormSubmitActionChangeTags(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('lead.changetags')) {
            return;
        }

        if (!$contact = $this->contactTracker->getContact()) {
            return;
        }

        $properties = $event->getAction()->getProperties();
        $addTags    = $properties['add_tags'] ?: [];
        $removeTags = $properties['remove_tags'] ?: [];

        $this->leadModel->modifyTags($contact, $addTags, $removeTags);
    }

    public function onFormSubmitActionAddUtmTags(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('lead.addutmtags')) {
            return;
        }

        if (!$contact = $this->contactTracker->getContact()) {
            return;
        }

        $queryReferer = $queryArray = [];

        parse_str($event->getRequest()->server->get('QUERY_STRING'), $queryArray);
        $refererURL       = $event->getRequest()->server->get('HTTP_REFERER');
        $refererParsedUrl = parse_url($refererURL);

        if (isset($refererParsedUrl['query'])) {
            parse_str($refererParsedUrl['query'], $queryReferer);
        }

        $utmValues = new UtmTag();
        $utmValues->setLead($contact);
        $utmValues->setQuery($event->getRequest()->query->all());
        $utmValues->setReferer($refererURL);
        $utmValues->setUrl($event->getRequest()->server->get('REQUEST_URI'));
        $utmValues->setDateAdded(new \Datetime());
        $utmValues->setRemoteHost($refererParsedUrl['host'] ?? null);
        $utmValues->setUserAgent($event->getRequest()->server->get('HTTP_USER_AGENT') ?? null);
        $utmValues->setUtmCampaign($queryArray['utm_campaign'] ?? $queryReferer['utm_campaign'] ?? null);
        $utmValues->setUtmContent($queryArray['utm_content'] ?? $queryReferer['utm_content'] ?? null);
        $utmValues->setUtmMedium($queryArray['utm_medium'] ?? $queryReferer['utm_medium'] ?? null);
        $utmValues->setUtmSource($queryArray['utm_source'] ?? $queryReferer['utm_source'] ?? null);
        $utmValues->setUtmTerm($queryArray['utm_term'] ?? $queryReferer['utm_term'] ?? null);

        $this->leadModel->getUtmTagRepository()->saveEntity($utmValues);
        $this->leadModel->setUtmTags($utmValues->getLead(), $utmValues);
    }

    public function onFormSubmitActionScoreContactsCompanies(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('lead.scorecontactscompanies')) {
            return;
        }

        if (!$contact = $this->contactTracker->getContact()) {
            return;
        }

        $properties = $event->getActionConfig();

        if (!empty($properties['score'])) {
            $this->leadModel->scoreContactsCompany($contact, $properties['score']);
        }
    }

    public function onFormSubmitActionRemoveFromDoNotContact(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('lead.remove_do_not_contact')) {
            return;
        }

        $formResults = $event->getResults();

        if (isset($formResults['email']) && !empty($formResults['email'])) {
            $this->emailModel->removeDoNotContact($formResults['email']);
        }
    }
}
