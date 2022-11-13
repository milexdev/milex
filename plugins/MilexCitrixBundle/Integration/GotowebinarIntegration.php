<?php

namespace MilexPlugin\MilexCitrixBundle\Integration;

/**
 * Class HubspotIntegration.
 */
class GotowebinarIntegration extends CitrixAbstractIntegration
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'Gotowebinar';
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return 'GoToWebinar';
    }
}
