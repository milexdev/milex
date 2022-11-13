<?php

namespace Milex\CampaignBundle\Controller\Api;

use Milex\ApiBundle\Controller\CommonApiController;
use Milex\ApiBundle\Serializer\Exclusion\FieldExclusionStrategy;
use Milex\CampaignBundle\Entity\Event;
use Milex\LeadBundle\Controller\LeadAccessTrait;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class EventApiController.
 */
class EventApiController extends CommonApiController
{
    use LeadAccessTrait;

    public function initialize(FilterControllerEvent $event)
    {
        $this->model                    = $this->getModel('campaign.event');
        $this->entityClass              = 'Milex\CampaignBundle\Entity\Event';
        $this->entityNameOne            = 'event';
        $this->entityNameMulti          = 'events';
        $this->serializerGroups         = ['campaignEventStandaloneDetails', 'campaignList'];
        $this->parentChildrenLevelDepth = 1;

        // Don't include campaign in children/parent arrays
        $this->addExclusionStrategy(new FieldExclusionStrategy(['campaign'], 1));

        parent::initialize($event);
    }

    /**
     * @param Event  $entity
     * @param string $action
     *
     * @return bool|mixed
     */
    protected function checkEntityAccess($entity, $action = 'view')
    {
        // Use the campaign for permission checks
        return parent::checkEntityAccess($entity->getCampaign(), $action);
    }
}
