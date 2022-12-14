<?php

namespace MilexPlugin\MilexGmailBundle\Integration;

use Milex\PluginBundle\Integration\AbstractIntegration;

class GmailIntegration extends AbstractIntegration
{
    public function getName()
    {
        return 'Gmail';
    }

    /**
     * Return's authentication method such as oauth2, oauth1a, key, etc.
     *
     * @return string
     */
    public function getAuthenticationType()
    {
        // Just use none for now and I'll build in "basic" later
        return 'none';
    }

    /**
     * Return array of key => label elements that will be converted to inputs to
     * obtain from the user.
     *
     * @return array
     */
    public function getRequiredKeyFields()
    {
        return [
            'secret' => 'milex.integration.gmail.secret',
        ];
    }
}
