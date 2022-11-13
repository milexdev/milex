<?php

namespace MilexPlugin\MilexSocialBundle\Controller\Api;

use Milex\ApiBundle\Controller\CommonApiController;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class TweetApiController.
 */
class TweetApiController extends CommonApiController
{
    /**
     * {@inheritdoc}
     */
    public function initialize(FilterControllerEvent $event)
    {
        $this->model           = $this->getModel('social.tweet');
        $this->entityClass     = 'MilexPlugin\MilexSocialBundle\Entity\Tweet';
        $this->entityNameOne   = 'tweet';
        $this->entityNameMulti = 'tweets';

        parent::initialize($event);
    }
}
