<?php

namespace MilexPlugin\MilexTagManagerBundle\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Milex\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Milex\LeadBundle\Entity\Tag as BaseTag;

class Tag extends BaseTag
{
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('lead_tags')
            ->setEmbeddable()
            ->setCustomRepositoryClass(TagRepository::class);
    }
}
