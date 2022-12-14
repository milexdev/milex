<?php

namespace MilexPlugin\MilexSocialBundle\Model;

use Milex\CoreBundle\Model\FormModel;
use MilexPlugin\MilexSocialBundle\Entity\Monitoring;
use MilexPlugin\MilexSocialBundle\Event as Events;
use MilexPlugin\MilexSocialBundle\Form\Type\MonitoringType;
use MilexPlugin\MilexSocialBundle\Form\Type\TwitterHashtagType;
use MilexPlugin\MilexSocialBundle\Form\Type\TwitterMentionType;
use MilexPlugin\MilexSocialBundle\SocialEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class MonitoringModel
 * {@inheritdoc}
 */
class MonitoringModel extends FormModel
{
    private $networkTypes = [
        'twitter_handle' => [
            'label' => 'milex.social.monitoring.type.list.twitter.handle',
            'form'  => TwitterMentionType::class,
        ],
        'twitter_hashtag' => [
            'label' => 'milex.social.monitoring.type.list.twitter.hashtag',
            'form'  => TwitterHashtagType::class,
        ],
    ];

    /**
     * {@inheritdoc}
     *
     * @param       $entity
     * @param       $formFactory
     * @param null  $action
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $params = [])
    {
        if (!$entity instanceof Monitoring) {
            throw new MethodNotAllowedHttpException(['Monitoring']);
        }

        if (!empty($action)) {
            $params['action'] = $action;
        }

        return $formFactory->create(MonitoringType::class, $entity, $params);
    }

    /**
     * Get a specific entity or generate a new one if id is empty.
     *
     * @param $id
     *
     * @return Monitoring|null
     */
    public function getEntity($id = null)
    {
        return $id ? parent::getEntity($id) : new Monitoring();
    }

    /**
     * {@inheritdoc}
     *
     * @param $action
     * @param $event
     * @param $entity
     * @param $isNew
     *
     * @throws \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, Event $event = null)
    {
        if (!$entity instanceof Monitoring) {
            throw new MethodNotAllowedHttpException(['Monitoring']);
        }

        switch ($action) {
            case 'pre_save':
                $name = SocialEvents::MONITOR_PRE_SAVE;
                break;
            case 'post_save':
                $name = SocialEvents::MONITOR_POST_SAVE;
                break;
            case 'pre_delete':
                $name = SocialEvents::MONITOR_PRE_DELETE;
                break;
            case 'post_delete':
                $name = SocialEvents::MONITOR_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (empty($event)) {
                $event = new Events\SocialEvent($entity, $isNew);
            }

            $this->dispatcher->dispatch($name, $event);

            return $event;
        } else {
            return null;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @var \MilexPlugin\MilexSocialBundle\Entity\Monitoring
     */
    public function saveEntity($monitoringEntity, $unlock = true)
    {
        // we're editing an existing record
        if (!$monitoringEntity->isNew()) {
            //increase the revision
            $revision = $monitoringEntity->getRevision();
            ++$revision;
            $monitoringEntity->setRevision($revision);
        } // is new
        else {
            $now = new \DateTime();
            $monitoringEntity->setDateAdded($now);
        }

        parent::saveEntity($monitoringEntity, $unlock);
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository()
    {
        return $this->em->getRepository('MilexSocialBundle:Monitoring');
    }

    /**
     * @return string
     */
    public function getPermissionBase()
    {
        return 'milexSocial:monitoring';
    }

    /**
     * @return array
     */
    public function getNetworkTypes()
    {
        $types = [];
        foreach ($this->networkTypes as $type => $data) {
            $types[$type] = $data['label'];
        }

        return $types;
    }

    /**
     * @param string $type
     *
     * @return |null
     */
    public function getFormByType($type)
    {
        return array_key_exists($type, $this->networkTypes) ? $this->networkTypes[$type]['form'] : null;
    }
}
