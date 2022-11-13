<?php

namespace Milex\CampaignBundle\Tests\Helper;

use Milex\CampaignBundle\Entity\Campaign;
use Milex\CampaignBundle\Event\CampaignLeadChangeEvent;
use Milex\CampaignBundle\Helper\CampaignEventHelper;
use Milex\CampaignBundle\Tests\CampaignTestAbstract;

class CampaignEventHelperTest extends CampaignTestAbstract
{
    public function testValidateLeadChangeTriggerWithEmptyCampaigns()
    {
        $eventDetails = new CampaignLeadChangeEvent(new Campaign(), [], 'badaction');
        $event        = [
            'properties' => [
                'campaigns' => [],
                'action'    => 'added',
            ],
            'campaign' => [
                'id' => null,
            ],
        ];
        $result = CampaignEventHelper::validateLeadChangeTrigger($eventDetails, $event);
        $this->assertFalse($result);
    }

    public function testValidateLeadChangeTriggerWithUnmatchingCampaignsAndInvalidAction()
    {
        $eventDetails = new CampaignLeadChangeEvent(new Campaign(), [], 'badaction');
        $event        = [
            'properties' => [
                'campaigns' => [3],
                'action'    => 'added',
            ],
            'campaign' => [
                'id' => 4,
            ],
        ];
        $result = CampaignEventHelper::validateLeadChangeTrigger($eventDetails, $event);
        $this->assertFalse($result);
    }

    public function testValidateLeadChangeTriggerWithMatchingCampaignsAndInvalidAction()
    {
        $eventDetails = new CampaignLeadChangeEvent(new Campaign(), [], 'removed');
        $event        = [
            'properties' => [
                'campaigns' => [3],
                'action'    => 'added',
            ],
            'campaign' => [
                'id' => 3,
            ],
        ];
        $result = CampaignEventHelper::validateLeadChangeTrigger($eventDetails, $event);
        $this->assertFalse($result);
    }

    public function testValidateLeadChangeTriggerWithMatchingCampaignsAndVariousActions()
    {
        $actions = [
            'added'   => true,
            'removed' => true,
            'invalid' => false,
        ];

        foreach ($actions as $action => $expectedResult) {
            $campaignId   = 3;
            $eventDetails = new CampaignLeadChangeEvent(new Campaign(), [], $action);
            $event        = [
                'properties' => [
                    'campaigns' => [$campaignId, 8],
                    'action'    => $action,
                ],
                'campaign' => [
                    'id' => $campaignId,
                ],
            ];
            $result = CampaignEventHelper::validateLeadChangeTrigger($eventDetails, $event);
            $this->assertSame($expectedResult, $result);
        }
    }
}
