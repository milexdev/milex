<?php

namespace MilexPlugin\MilexOutlookBundle\Integration;

use Milex\CoreBundle\Helper\UrlHelper;
use Milex\PluginBundle\Integration\AbstractIntegration;

class OutlookIntegration extends AbstractIntegration
{
    public function getName()
    {
        return 'Outlook';
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
            'secret' => 'milex.integration.outlook.secret',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param $section
     *
     * @return string
     */
    public function getFormNotes($section)
    {
        if ('custom' === $section) {
            return [
                'template'   => 'MilexOutlookBundle:Integration:form.html.php',
                'parameters' => [
                    'milexUrl' => UrlHelper::rel2abs('/index.php'),
                ],
            ];
        }

        return parent::getFormNotes($section);
    }
}
