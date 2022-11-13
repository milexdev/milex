<?php

namespace Milex\AssetBundle\Event;

use Gaufrette\Adapter;
use Milex\CoreBundle\Event\CommonEvent;
use Milex\PluginBundle\Integration\AbstractIntegration;
use Milex\PluginBundle\Integration\UnifiedIntegrationInterface;

/**
 * Class RemoteAssetBrowseEvent.
 */
class RemoteAssetBrowseEvent extends CommonEvent
{
    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var AbstractIntegration
     */
    private $integration;

    public function __construct(UnifiedIntegrationInterface $integration)
    {
        $this->integration = $integration;
    }

    /**
     * @return Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return AbstractIntegration
     */
    public function getIntegration()
    {
        return $this->integration;
    }

    /**
     * @return $this
     */
    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }
}
