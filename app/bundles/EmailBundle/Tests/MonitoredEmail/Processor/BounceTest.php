<?php

namespace Milex\EmailBundle\Tests\MonitoredEmail\Processor;

use Milex\CoreBundle\Translation\Translator;
use Milex\EmailBundle\Entity\Email;
use Milex\EmailBundle\Entity\Stat;
use Milex\EmailBundle\Entity\StatRepository;
use Milex\EmailBundle\MonitoredEmail\Message;
use Milex\EmailBundle\MonitoredEmail\Processor\Bounce;
use Milex\EmailBundle\MonitoredEmail\Search\ContactFinder;
use Milex\EmailBundle\MonitoredEmail\Search\Result;
use Milex\EmailBundle\Tests\MonitoredEmail\Transport\TestTransport;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Model\DoNotContact;
use Milex\LeadBundle\Model\LeadModel;
use Monolog\Logger;

class BounceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testdox Test that the transport interface processes the message appropriately
     *
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\Bounce::process()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\Bounce::updateStat()
     * @covers  \Milex\EmailBundle\Swiftmailer\Transport\BounceProcessorInterface::processBounce()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::setStat()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::getStat()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::setContacts()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::getContacts()
     */
    public function testProcessorInterfaceProcessesMessage()
    {
        $transport     = new TestTransport(new \Swift_Events_SimpleEventDispatcher());
        $contactFinder = $this->getMockBuilder(ContactFinder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contactFinder->method('find')
            ->willReturnCallback(
                function ($email, $bounceAddress) {
                    $stat = new Stat();

                    $lead = new Lead();
                    $lead->setEmail($email);
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

        $statRepo = $this->getMockBuilder(StatRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statRepo->expects($this->once())
            ->method('saveEntity');

        $leadModel = $this->getMockBuilder(LeadModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doNotContact = $this->createMock(DoNotContact::class);

        $bouncer = new Bounce($transport, $contactFinder, $statRepo, $leadModel, $translator, $logger, $doNotContact);

        $message = new Message();
        $this->assertTrue($bouncer->process($message));
    }

    /**
     * @testdox Test that the message is processed appropriately
     *
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\Bounce::process()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\Bounce::updateStat()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::setStat()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::getStat()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::setContacts()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::getContacts()
     */
    public function testContactIsFoundFromMessageAndDncRecordAdded()
    {
        $transport     = new \Swift_Transport_NullTransport(new \Swift_Events_SimpleEventDispatcher());
        $contactFinder = $this->getMockBuilder(ContactFinder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $contactFinder->method('find')
            ->willReturnCallback(
                function ($email, $bounceAddress) {
                    $stat = new Stat();

                    $lead = new Lead();
                    $lead->setEmail($email);
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

        $statRepo = $this->getMockBuilder(StatRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statRepo->expects($this->once())
            ->method('saveEntity');

        $leadModel = $this->getMockBuilder(LeadModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doNotContact = $this->createMock(DoNotContact::class);

        $bouncer = new Bounce($transport, $contactFinder, $statRepo, $leadModel, $translator, $logger, $doNotContact);

        $message            = new Message();
        $message->to        = ['contact+bounce_123abc@test.com' => null];
        $message->dsnReport = <<<'DSN'
Original-Recipient: sdfgsdfg@seznan.cz
Final-Recipient: rfc822;sdfgsdfg@seznan.cz
Action: failed
Status: 5.4.4
Diagnostic-Code: DNS; Host not found
DSN;

        $this->assertTrue($bouncer->process($message));
    }
}
