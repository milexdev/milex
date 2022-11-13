<?php

namespace Milex\CampaignBundle\Tests\Membership\Action;

use Milex\CampaignBundle\Entity\Campaign;
use Milex\CampaignBundle\Entity\Lead as CampaignMember;
use Milex\CampaignBundle\Entity\LeadEventLogRepository;
use Milex\CampaignBundle\Entity\LeadRepository;
use Milex\CampaignBundle\Membership\Action\Adder;
use Milex\CampaignBundle\Membership\Exception\ContactCannotBeAddedToCampaignException;
use Milex\LeadBundle\Entity\Lead;

class AdderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LeadRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $leadRepository;

    /**
     * @var LeadEventLogRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private $leadEventLogRepository;

    protected function setUp(): void
    {
        $this->leadRepository         = $this->createMock(LeadRepository::class);
        $this->leadEventLogRepository = $this->createMock(LeadEventLogRepository::class);
    }

    public function testNewMemberAdded()
    {
        $campaign = $this->createMock(Campaign::class);
        $campaign->method('getId')
            ->willReturn(1);
        $campaign->method('allowRestart')
            ->willReturn(true);

        $contact = $this->createMock(Lead::class);
        $contact->method('getId')
            ->WillReturn(2);

        $this->leadEventLogRepository->method('hasBeenInCampaignRotation')
            ->with(2, 1, 1)
            ->willReturn(true);

        $this->leadRepository->expects($this->once())
            ->method('saveEntity');

        $campaignMember = $this->getAdder()->createNewMembership($contact, $campaign, true);

        $this->assertEquals($contact, $campaignMember->getLead());
        $this->assertEquals($campaign, $campaignMember->getCampaign());
        $this->assertEquals(true, $campaignMember->wasManuallyAdded());
        $this->assertEquals(2, $campaignMember->getRotation());
    }

    public function testManuallyRemovedAddedBackWhenManualActionAddsTheMember()
    {
        $campaignMember = new CampaignMember();
        $campaignMember->setManuallyRemoved(true);
        $campaignMember->setRotation(1);
        $campaign = new Campaign();
        $campaign->setAllowRestart(true);
        $campaignMember->setCampaign($campaign);

        $this->getAdder()->updateExistingMembership($campaignMember, true);

        $this->assertEquals(true, $campaignMember->wasManuallyAdded());
        $this->assertEquals(2, $campaignMember->getRotation());
    }

    public function testFilterRemovedAddedBackWhenManualActionAddsTheMember()
    {
        $campaignMember = new CampaignMember();
        $campaignMember->setManuallyRemoved(true);
        $campaignMember->setRotation(1);
        $campaignMember->setDateLastExited(new \DateTime());
        $campaign = new Campaign();
        $campaign->setAllowRestart(true);
        $campaignMember->setCampaign($campaign);

        $this->getAdder()->updateExistingMembership($campaignMember, false);

        $this->assertEquals(false, $campaignMember->wasManuallyAdded());
        $this->assertEquals(2, $campaignMember->getRotation());
    }

    public function testManuallyRemovedIsNotAddedBackWhenFilterActionAddsTheMember()
    {
        $this->expectException(ContactCannotBeAddedToCampaignException::class);

        $campaignMember = new CampaignMember();
        $campaignMember->setManuallyRemoved(true);
        $campaignMember->setRotation(1);
        $campaign = new Campaign();
        $campaign->setAllowRestart(false);
        $campaignMember->setCampaign($campaign);

        $this->getAdder()->updateExistingMembership($campaignMember, false);
    }

    /**
     * @return Adder
     */
    private function getAdder()
    {
        return new Adder($this->leadRepository, $this->leadEventLogRepository);
    }
}
