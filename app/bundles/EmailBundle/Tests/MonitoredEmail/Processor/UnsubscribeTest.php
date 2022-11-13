<?php

namespace Milex\EmailBundle\Tests\MonitoredEmail\Processor;

use Milex\CoreBundle\Translation\Translator;
use Milex\EmailBundle\Entity\Email;
use Milex\EmailBundle\Entity\Stat;
use Milex\EmailBundle\MonitoredEmail\Message;
use Milex\EmailBundle\MonitoredEmail\Processor\Unsubscribe;
use Milex\EmailBundle\MonitoredEmail\Search\ContactFinder;
use Milex\EmailBundle\MonitoredEmail\Search\Result;
use Milex\EmailBundle\Tests\MonitoredEmail\Transport\TestTransport;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Model\DoNotContact;
use Monolog\Logger;

class UnsubscribeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testdox Test that the transport interface processes the message appropriately
     *
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\Unsubscribe::process()
     * @covers  \Milex\EmailBundle\Swiftmailer\Transport\UnsubscriptionProcessorInterface::processUnsubscription()
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
                function ($email) {
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

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doNotContact = $this->getMockBuilder(DoNotContact::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processor = new Unsubscribe($transport, $contactFinder, $translator, $logger, $doNotContact);

        $message = new Message();
        $this->assertTrue($processor->process($message));
    }

    /**
     * @testdox Test that the message is processed appropriately
     *
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\Unsubscribe::process()
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
                function ($email) {
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

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doNotContact = $this->getMockBuilder(DoNotContact::class)
            ->disableOriginalConstructor()
            ->getMock();

        $processor = new Unsubscribe($transport, $contactFinder, $translator, $logger, $doNotContact);

        $message     = new Message();
        $message->to = ['contact+unsubscribe_123abc@test.com' => null];
        $this->assertTrue($processor->process($message));
    }
}
