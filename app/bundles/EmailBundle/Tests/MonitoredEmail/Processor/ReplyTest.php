<?php

namespace Milex\EmailBundle\Tests\MonitoredEmail\Processor;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Milex\EmailBundle\EmailEvents;
use Milex\EmailBundle\Entity\Email;
use Milex\EmailBundle\Entity\EmailReply;
use Milex\EmailBundle\Entity\Stat;
use Milex\EmailBundle\Entity\StatRepository;
use Milex\EmailBundle\Event\EmailReplyEvent;
use Milex\EmailBundle\MonitoredEmail\Message;
use Milex\EmailBundle\MonitoredEmail\Processor\Reply;
use Milex\EmailBundle\MonitoredEmail\Search\ContactFinder;
use Milex\EmailBundle\MonitoredEmail\Search\Result;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Model\LeadModel;
use Milex\LeadBundle\Tracker\ContactTracker;
use Monolog\Logger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ReplyTest extends \PHPUnit\Framework\TestCase
{
    private $statRepo;
    private $contactFinder;
    private $leadModel;
    private $dispatcher;
    private $logger;
    private $contactTracker;

    /**
     * @var Reply
     */
    private $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statRepo       = $this->createMock(StatRepository::class);
        $this->contactFinder  = $this->createMock(ContactFinder::class);
        $this->leadModel      = $this->createMock(LeadModel::class);
        $this->leadModel      = $this->createMock(LeadModel::class);
        $this->dispatcher     = $this->createMock(EventDispatcherInterface::class);
        $this->logger         = $this->createMock(Logger::class);
        $this->contactTracker = $this->createMock(ContactTracker::class);
        $this->processor      = new Reply(
            $this->statRepo,
            $this->contactFinder,
            $this->leadModel,
            $this->dispatcher,
            $this->logger,
            $this->contactTracker
        );
    }

    /**
     * @testdox Test that the message is processed appropriately
     *
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\Reply::process()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::setStat()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::getStat()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::setContacts()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::getContacts()
     */
    public function testContactIsFoundFromMessageAndDncRecordAdded()
    {
        // This tells us that a reply was found and processed
        $this->statRepo->expects($this->once())
            ->method('saveEntity');

        $this->contactFinder->method('findByHash')
            ->willReturnCallback(
                function ($hash) {
                    $stat = new Stat();
                    $stat->setTrackingHash($hash);

                    $lead = new Lead();
                    $lead->setEmail('contact@email.com');
                    $stat->setLead($lead);

                    $email = new Email();
                    $stat->setEmail($email);

                    $result = new Result();
                    $result->setStat($stat);
                    $result->setContacts(
                        [
                            $lead,
                        ]
                    );

                    return $result;
                }
            );

        $message              = new Message();
        $message->fromAddress = 'contact@email.com';
        $message->textHtml    = <<<'BODY'
<img src="http://test.com/email/123abc.gif" />
BODY;

        $this->processor->process($message);
    }

    public function testCreateReplyByHashIfStatNotFound()
    {
        $trackingHash = '@Stat#';

        $this->statRepo->expects($this->once())
            ->method('findOneBy')
            ->with(['trackingHash' => $trackingHash])
            ->willReturn(null);

        $this->expectException(EntityNotFoundException::class);

        $this->processor->createReplyByHash($trackingHash, 'api-msg1d');
    }

    public function testCreateReplyByHash()
    {
        $trackingHash = '@Stat#';
        $stat         = $this->createMock(Stat::class);
        $contact      = $this->createMock(Lead::class);

        $this->statRepo->expects($this->once())
            ->method('findOneBy')
            ->with(['trackingHash' => $trackingHash])
            ->willReturn($stat);

        $stat->expects($this->once())
            ->method('setIsRead')
            ->with(true);

        $stat->expects($this->once())
            ->method('getDateRead')
            ->willReturn(null);

        $stat->expects($this->once())
            ->method('setDateRead')
            ->with($this->isInstanceOf(\DateTime::class));

        $stat->expects($this->any())
            ->method('getReplies')
            ->willReturn(new ArrayCollection());

        $stat->expects($this->once())
            ->method('addReply')
            ->with($this->callback(function (EmailReply $emailReply) use ($stat) {
                $this->assertSame($stat, $emailReply->getStat());
                $this->assertSame('api-msg1d', $emailReply->getMessageId());

                return true;
            }));

        $this->statRepo->expects($this->once())
            ->method('saveEntity')
            ->with($this->isInstanceOf(Stat::class));

        $stat->expects($this->exactly(2))
            ->method('getLead')
            ->willReturn($contact);

        $this->dispatcher->expects($this->once())
            ->method('hasListeners')
            ->with(EmailEvents::EMAIL_ON_REPLY)
            ->willReturn(true);

        $this->contactTracker->expects($this->once())
            ->method('setTrackedContact')
            ->with($contact);

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(EmailEvents::EMAIL_ON_REPLY, $this->isInstanceOf(EmailReplyEvent::class));

        $this->processor->createReplyByHash($trackingHash, 'api-msg1d');
    }
}
