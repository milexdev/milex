<?php

namespace Milex\LeadBundle\Controller\Api;

use Milex\ApiBundle\Controller\CommonApiController;
use Milex\LeadBundle\Controller\LeadAccessTrait;
use Milex\LeadBundle\Entity\LeadNote;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class NoteApiController.
 */
class NoteApiController extends CommonApiController
{
    use LeadAccessTrait;

    public function initialize(FilterControllerEvent $event)
    {
        $this->model            = $this->getModel('lead.note');
        $this->entityClass      = LeadNote::class;
        $this->entityNameOne    = 'note';
        $this->entityNameMulti  = 'notes';
        $this->serializerGroups = ['leadNoteDetails', 'leadList'];

        parent::initialize($event);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Milex\LeadBundle\Entity\Lead &$entity
     * @param                                $parameters
     * @param                                $form
     * @param string                         $action
     */
    protected function preSaveEntity(&$entity, $form, $parameters, $action = 'edit')
    {
        if (!empty($parameters['lead'])) {
            $lead = $this->checkLeadAccess($parameters['lead'], $action);

            if ($lead instanceof Response) {
                return $lead;
            }

            $entity->setLead($lead);
            unset($parameters['lead']);
        } elseif ('new' === $action) {
            return $this->returnError('lead ID is mandatory', Response::HTTP_BAD_REQUEST);
        }
    }
}
