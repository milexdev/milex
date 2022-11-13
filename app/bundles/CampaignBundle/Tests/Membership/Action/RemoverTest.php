<?php

namespace Milex\CampaignBundle\Tests\Membership\Action;

use Milex\CampaignBundle\Entity\Lead as CampaignMember;
use Milex\CampaignBundle\Entity\LeadEventLogRepository;
use Milex\CampaignBundle\Entity\LeadRepository;
use Milex\CampaignBundle\Membership\Action\Remover;
use Milex\CampaignBundle\Membership\Exception\ContactAlreadyRemovedFromCampaignException;
use Milex\CoreBundle\Templating\Helper\DateHelper;
use Symfony\Component\Translation\TranslatorInterface;

class RemoverTest extends \PHPUnit\Framework\TestCase
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

    public function testMemberHasDateExitedSetWithForcedExit()
    {
        $campaignMember = new CampaignMember();
        $campaignMember->setManuallyRemoved(false);

        $this->leadEventLogRepository->expects($this->once())
            ->method('unscheduleEvents');

        $this->getRemover()->updateExistingMembership($campaignMember, true);

        $this->assertInstanceOf(\DateTime::class, $campaignMember->getDateLastExited());
    }

    public function testMemberHasDateExistedSetToNullWhenRemovedByFilter()
    {
        $campaignMember = new CampaignMember();
        $campaignMember->setManuallyRemoved(false);

        $this->leadEventLogRepository->expects($this->once())
            ->method('unscheduleEvents');

        $this->getRemover()->updateExistingMembership($campaignMember, false);

        $this->assertNull($campaignMember->getDateLastExited());
    }

    public function testExceptionThrownWhenMemberIsAlreadyRemoved()
    {
        $this->expectException(ContactAlreadyRemovedFromCampaignException::class);

        $campaignMember = new CampaignMember();
        $campaignMember->setManuallyRemoved(true);

        $this->getRemover()->updateExistingMembership($campaignMember, false);
    }

    /**
     * @return Remover
     */
    private function getRemover()
    {
        $translator     = $this->createMock(TranslatorInterface::class);
        $dateTimeHelper = $this->createMock(DateHelper::class);

        return new Remover($this->leadRepository, $this->leadEventLogRepository, $translator, $dateTimeHelper);
    }
}
