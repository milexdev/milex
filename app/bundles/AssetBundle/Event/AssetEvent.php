<?php

namespace Milex\AssetBundle\Event;

use Milex\AssetBundle\Entity\Asset;
use Milex\CoreBundle\Event\CommonEvent;

/**
 * Class AssetEvent.
 */
class AssetEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Asset $asset, $isNew = false)
    {
        $this->entity = $asset;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Asset entity.
     *
     * @return Asset
     */
    public function getAsset()
    {
        return $this->entity;
    }

    /**
     * Sets the Asset entity.
     */
    public function setAsset(Asset $asset)
    {
        $this->entity = $asset;
    }
}
