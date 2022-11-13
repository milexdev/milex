<?php

namespace Milex\SmsBundle\Helper;

use Doctrine\ORM\EntityManager;
use libphonenumber\PhoneNumberFormat;
use Milex\CoreBundle\Helper\PhoneNumberHelper;
use Milex\LeadBundle\Entity\DoNotContact as DoNotContactEntity;
use Milex\LeadBundle\Entity\LeadRepository;
use Milex\LeadBundle\Model\DoNotContact;
use Milex\LeadBundle\Model\LeadModel;
use Milex\PluginBundle\Helper\IntegrationHelper;
use Milex\SmsBundle\Model\SmsModel;

class SmsHelper
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var PhoneNumberHelper
     */
    protected $phoneNumberHelper;

    /**
     * @var SmsModel
     */
    protected $smsModel;

    /**
     * @var IntegrationHelper
     */
    protected $integrationHelper;

    /**
     * @var DoNotContact
     */
    private $doNotContact;

    public function __construct(
        EntityManager $em,
        LeadModel $leadModel,
        PhoneNumberHelper $phoneNumberHelper,
        SmsModel $smsModel,
        IntegrationHelper $integrationHelper,
        DoNotContact $doNotContact
    ) {
        $this->em                   = $em;
        $this->leadModel            = $leadModel;
        $this->phoneNumberHelper    = $phoneNumberHelper;
        $this->smsModel             = $smsModel;
        $this->integrationHelper    = $integrationHelper;
        $this->doNotContact         = $doNotContact;
    }

    public function unsubscribe($number)
    {
        $number = $this->phoneNumberHelper->format($number, PhoneNumberFormat::E164);

        /** @var LeadRepository $repo */
        $repo = $this->em->getRepository('MilexLeadBundle:Lead');

        $args = [
            'filter' => [
                'force' => [
                    [
                        'column' => 'mobile',
                        'expr'   => 'eq',
                        'value'  => $number,
                    ],
                ],
            ],
        ];

        $leads = $repo->getEntities($args);

        if (!empty($leads)) {
            $lead = array_shift($leads);
        } else {
            // Try to find the lead based on the given phone number
            $args['filter']['force'][0]['column'] = 'phone';

            $leads = $repo->getEntities($args);

            if (!empty($leads)) {
                $lead = array_shift($leads);
            } else {
                return false;
            }
        }

        return $this->doNotContact->addDncForContact($lead->getId(), 'sms', DoNotContactEntity::UNSUBSCRIBED);
    }

    /**
     * @return bool
     */
    public function getDisableTrackableUrls()
    {
        $integration = $this->integrationHelper->getIntegrationObject('Twilio');
        $settings    = $integration->getIntegrationSettings()->getFeatureSettings();

        return !empty($settings['disable_trackable_urls']) ? true : false;
    }
}
