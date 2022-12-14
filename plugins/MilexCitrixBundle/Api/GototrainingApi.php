<?php

namespace MilexPlugin\MilexCitrixBundle\Api;

use Milex\PluginBundle\Exception\ApiErrorException;

class GototrainingApi extends CitrixApi
{
    /**
     * @param string $operation
     * @param string $method
     * @param string $route
     *
     * @return mixed|string
     *
     * @throws ApiErrorException
     */
    public function request($operation, array $parameters = [], $method = 'GET', $route = 'rest')
    {
        $settings = [
            'module'     => 'G2T',
            'method'     => $method,
            'parameters' => $parameters,
        ];

        if (preg_match('/start$/', $operation)) {
            return parent::_request($operation, $settings, $route);
        }

        return parent::_request($operation, $settings,
            sprintf('%s/organizers/%s', $route, $this->integration->getOrganizerKey()));
    }
}
