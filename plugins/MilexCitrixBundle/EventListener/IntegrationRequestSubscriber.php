<?php

namespace MilexPlugin\MilexCitrixBundle\EventListener;

use Milex\PluginBundle\Event\PluginIntegrationRequestEvent;
use Milex\PluginBundle\PluginEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class StatsSubscriber.
 */
class IntegrationRequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::PLUGIN_ON_INTEGRATION_REQUEST => [
                'getParameters',
                0,
            ],
        ];
    }

    /**
     * @throws \Exception
     */
    public function getParameters(PluginIntegrationRequestEvent $requestEvent)
    {
        if (false !== strpos($requestEvent->getUrl(), 'oauth/v2/token')) {
            $authorization = $this->getAuthorization($requestEvent->getParameters());
            $requestEvent->setHeaders([
                'Authorization' => sprintf('Basic %s', base64_encode($authorization)),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ]);
        }
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    private function getAuthorization(array $parameters)
    {
        if (empty($parameters['client_id'])) {
            throw new \Exception('No client ID given.');
        }

        if (empty($parameters['client_secret'])) {
            throw new \Exception('No client secret given.');
        }

        return sprintf('%s:%s', $parameters['client_id'], $parameters['client_secret']);
    }
}
