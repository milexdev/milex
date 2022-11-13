<?php

namespace MilexPlugin\MilexCitrixBundle\Integration;

/**
 * Class HubspotIntegration.
 */
class GotoassistIntegration extends CitrixAbstractIntegration
{
    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName()
    {
        return 'Gotoassist';
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return 'GoToAssist';
    }
}
