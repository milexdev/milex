<?php

namespace MilexPlugin\MilexTagManagerBundle\Model;

use Milex\LeadBundle\Model\TagModel as BaseTagModel;
use MilexPlugin\MilexTagManagerBundle\Entity\Tag;
use MilexPlugin\MilexTagManagerBundle\Form\Type\TagEntityType;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class TagModel
 * {@inheritdoc}
 */
class TagModel extends BaseTagModel
{
    /**
     * {@inheritdoc}
     *
     * @return object
     */
    public function getRepository()
    {
        return $this->em->getRepository(Tag::class);
    }

    /**
     * {@inheritdoc}
     *
     * @param Tag   $entity
     * @param       $formFactory
     * @param null  $action
     * @param array $options
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function createForm($entity, $formFactory, $action = null, $options = [])
    {
        if (!$entity instanceof \Milex\LeadBundle\Entity\Tag) {
            throw new MethodNotAllowedHttpException(['Tag']);
        }

        if (!empty($action)) {
            $options['action'] = $action;
        }

        return $formFactory->create(TagEntityType::class, $entity, $options);
    }
}
