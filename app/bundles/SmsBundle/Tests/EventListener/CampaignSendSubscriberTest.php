<?php

namespace Milex\SmsBundle\Tests\EventListener;

use Milex\CampaignBundle\Event\CampaignExecutionEvent;
use Milex\LeadBundle\Entity\Lead;
use Milex\SmsBundle\Entity\Sms;
use Milex\SmsBundle\EventListener\CampaignSendSubscriber;
use Milex\SmsBundle\Model\SmsModel;
use Milex\SmsBundle\Sms\TransportChain;
use PHPUnit\Framework\MockObject\MockObject;

class CampaignSendSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var mixed[]
     */
    private $args;

    /**
     * @var MockObject|SmsModel
     */
    private $smsModel;

    /**
     * @var MockObject|TransportChain
     */
    private $transportChain;

    protected function setUp(): void
    {
        $this->smsModel       = $this->createMock(SmsModel::class);
        $this->transportChain = $this->createMock(TransportChain::class);

        $lead = new Lead();
        $lead->setId(1);
        $this->args = [
            'lead'            => $lead,
            'event'           => [
                'type'       => 'sms.send_text_sms',
                'properties' => ['sms' => 1],
            ],
            'eventDetails'    => [],
            'systemTriggered' => true,
            'eventSettings'   => [],
        ];
    }

    public function testSendDeletedSms(): void
    {
        $this->smsModel->expects(self::once())->method('getEntity')->willReturn(null);

        $event = new CampaignExecutionEvent($this->args, false, null);

        $this->CampaignSendSubscriber()->onCampaignTriggerAction($event);
        self::assertTrue((bool) $event->getResult()['failed']);
        self::assertSame('milex.sms.campaign.failed.missing_entity', $event->getResult()['reason']);
    }

    public function testSendUnpublishedSms(): void
    {
        $lead = new Lead();
        $lead->setId(1);
        $sms = new Sms();
        $sms->setIsPublished(false);
        $this->smsModel->expects(self::once())->method('getEntity')->willReturn($sms);

        $event = new CampaignExecutionEvent($this->args, false, null);

        $this->CampaignSendSubscriber()->onCampaignTriggerAction($event);
        self::assertTrue((bool) $event->getResult()['failed']);
        self::assertSame('milex.sms.campaign.failed.unpublished', $event->getResult()['reason']);
    }

    private function CampaignSendSubscriber(): CampaignSendSubscriber
    {
        return new CampaignSendSubscriber($this->smsModel, $this->transportChain);
    }
}
