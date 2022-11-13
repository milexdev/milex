<?php

namespace Milex\EmailBundle\Tests\MonitoredEmail\Processor\Unsubscription;

use Milex\EmailBundle\MonitoredEmail\Exception\UnsubscriptionNotFound;
use Milex\EmailBundle\MonitoredEmail\Message;
use Milex\EmailBundle\MonitoredEmail\Processor\Unsubscription\Parser;
use Milex\EmailBundle\MonitoredEmail\Processor\Unsubscription\UnsubscribedEmail;

class ParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @testdox Test that an email is found inside a feedback report
     *
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\Unsubscription\Parser::parse()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\Unsubscription\UnsubscribedEmail::getContactEmail()
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\Unsubscription\UnsubscribedEmail::getUnsubscriptionAddress()
     */
    public function testThatReplyIsDetectedThroughTrackingPixel()
    {
        $message              = new Message();
        $message->fromAddress = 'hello@hello.com';
        $message->to          = [
            'test+unsubscribe@test.com' => 'Test Test',
        ];

        $parser = new Parser($message);

        $unsubscribedEmail = $parser->parse();
        $this->assertInstanceOf(UnsubscribedEmail::class, $unsubscribedEmail);

        $this->assertEquals('hello@hello.com', $unsubscribedEmail->getContactEmail());
        $this->assertEquals('test+unsubscribe@test.com', $unsubscribedEmail->getUnsubscriptionAddress());
    }

    /**
     * @testdox Test that an exeption is thrown if a unsubscription email is not found
     *
     * @covers  \Milex\EmailBundle\MonitoredEmail\Processor\Unsubscription\Parser::parse()
     */
    public function testExceptionIsThrownWithUnsubscribeNotFound()
    {
        $this->expectException(UnsubscriptionNotFound::class);

        $message = new Message();
        $parser  = new Parser($message);

        $parser->parse();
    }
}
