<?php

namespace Milex\EmailBundle\Tests\MonitoredEmail\Processor;

use Milex\CoreBundle\Translation\Translator;
use Milex\EmailBundle\Entity\Email;
use Milex\EmailBundle\Entity\Stat;
use Milex\EmailBundle\MonitoredEmail\Message;
use Milex\EmailBundle\MonitoredEmail\Processor\FeedbackLoop;
use Milex\EmailBundle\MonitoredEmail\Search\ContactFinder;
use Milex\EmailBundle\MonitoredEmail\Search\Result;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Model\DoNotContact;
use Monolog\Logger;

class FeedbackLoopTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testdox Test that the message is processed appropriately
     *
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\FeedbackLoop::process()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::setStat()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::getStat()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::setContacts()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Search\Result::getContacts()
     */
    public function testContactIsFoundFromMessage()
    {
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

        $processor = new FeedbackLoop($contactFinder, $translator, $logger, $doNotContact);

        $message            = new Message();
        $message->fblReport = <<<'BODY'
Feedback-Type: abuse
User-Agent: SomeGenerator/1.0
Version: 1
Original-Mail-From: <somespammer@example.net>
Original-Rcpt-To: <user@example.com>
Received-Date: Thu, 8 Mar 2005 14:00:00 EDT
Source-IP: 192.0.2.2
Authentication-Results: mail.example.com
               smtp.mail=somespammer@example.com;
               spf=fail
Reported-Domain: example.net
Reported-Uri: http://example.net/earn_money.html
Reported-Uri: mailto:user@example.com
Removal-Recipient: user@example.com
BODY;

        $this->assertTrue($processor->process($message));
    }
}
