<?php

namespace Milex\EmailBundle\Tests\EventListener;

use Milex\EmailBundle\EventListener\EmailToUserSubscriber;
use Milex\EmailBundle\Exception\EmailCouldNotBeSentException;
use Milex\EmailBundle\Model\SendEmailToUser;
use Milex\LeadBundle\Entity\Lead;
use Milex\PointBundle\Entity\TriggerEvent;
use Milex\PointBundle\Event\TriggerExecutedEvent;

class EmailToUserSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /** @var array */
    private $config = [
        'useremail' => [
            'email' => 33,
        ],
        'user_id'  => [6, 7],
        'to_owner' => true,
        'to'       => 'hello@there.com, bob@bobek.cz',
        'bcc'      => 'hidden@translation.in',
    ];

    public function testOnCampaignTriggerActionSendEmailToUserWithSendingTheEmail()
    {
        $lead = new Lead();

        $mockSendEmailToUser = $this->getMockBuilder(SendEmailToUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subscriber = new EmailToUserSubscriber($mockSendEmailToUser);

        $mockSendEmailToUser->expects($this->once())
            ->method('sendEmailToUsers')
            ->with($this->config, $lead);

        $mockSendEmailToUser->expects($this->once())
            ->method('sendEmailToUsers')
            ->with($this->config, $lead);

        $triggerEvent = new TriggerEvent();
        $triggerEvent->setProperties($this->config);

        $event = new TriggerExecutedEvent($triggerEvent, $lead);

        $subscriber->onEmailToUser($event);

        $this->assertTrue($event->getResult());
    }

    public function testOnCampaignTriggerActionSendEmailToUserWithError()
    {
        $lead = new Lead();

        $mockSendEmailToUser = $this->getMockBuilder(SendEmailToUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subscriber = new EmailToUserSubscriber($mockSendEmailToUser);

        $mockSendEmailToUser->expects($this->once())
            ->method('sendEmailToUsers')
            ->with($this->config, $lead);

        $mockSendEmailToUser->expects($this->once())
            ->method('sendEmailToUsers')
            ->with($this->config, $lead)
            ->will($this->throwException(new EmailCouldNotBeSentException()));

        $triggerEvent = new TriggerEvent();
        $triggerEvent->setProperties($this->config);

        $event = new TriggerExecutedEvent($triggerEvent, $lead);

        $subscriber->onEmailToUser($event);

        $this->assertFalse($event->getResult());
    }
}
