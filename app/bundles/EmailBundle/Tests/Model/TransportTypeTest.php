<?php

namespace Milex\EmailBundle\Tests\Model;

use Milex\EmailBundle\Model\TransportType;

class TransportTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testGetTransportTypes()
    {
        $transportType = new TransportType();

        $expected = [
            'milex.transport.amazon'       => 'milex.email.config.mailer_transport.amazon',
            'milex.transport.amazon_api'   => 'milex.email.config.mailer_transport.amazon_api',
            'milex.transport.elasticemail' => 'milex.email.config.mailer_transport.elasticemail',
            'gmail'                         => 'milex.email.config.mailer_transport.gmail',
            'milex.transport.mandrill'     => 'milex.email.config.mailer_transport.mandrill',
            'milex.transport.mailjet'      => 'milex.email.config.mailer_transport.mailjet',
            'smtp'                          => 'milex.email.config.mailer_transport.smtp',
            'milex.transport.postmark'     => 'milex.email.config.mailer_transport.postmark',
            'milex.transport.sendgrid'     => 'milex.email.config.mailer_transport.sendgrid',
            'milex.transport.pepipost'     => 'milex.email.config.mailer_transport.pepipost',
            'milex.transport.sendgrid_api' => 'milex.email.config.mailer_transport.sendgrid_api',
            'sendmail'                      => 'milex.email.config.mailer_transport.sendmail',
            'milex.transport.sparkpost'    => 'milex.email.config.mailer_transport.sparkpost',
        ];

        $this->assertSame($expected, $transportType->getTransportTypes());
    }

    public function testSmtpService()
    {
        $transportType = new TransportType();

        $expected = '"smtp"';

        $this->assertSame($expected, $transportType->getSmtpService());
    }

    public function testAmazonService()
    {
        $transportType = new TransportType();

        $expected = '"milex.transport.amazon","milex.transport.amazon_api"';

        $this->assertSame($expected, $transportType->getAmazonService());
    }

    public function testDoNotNeedRegion()
    {
        $transportType = new TransportType();

        $expected = '"milex.transport.elasticemail","gmail","milex.transport.mandrill","milex.transport.mailjet","smtp","milex.transport.postmark","milex.transport.sendgrid","milex.transport.pepipost","milex.transport.sendgrid_api","sendmail","milex.transport.sparkpost"';

        $this->assertSame($expected, $transportType->getServiceDoNotNeedAmazonRegion());
    }

    public function testMailjetService()
    {
        $transportType = new TransportType();

        $expected = '"milex.transport.mailjet"';

        $this->assertSame($expected, $transportType->getMailjetService());
    }

    public function testRequiresLogin()
    {
        $transportType = new TransportType();

        $expected = '"milex.transport.mailjet","milex.transport.sendgrid","milex.transport.pepipost","milex.transport.elasticemail","milex.transport.amazon","milex.transport.amazon_api","milex.transport.postmark","gmail"';

        $this->assertSame($expected, $transportType->getServiceRequiresUser());
    }

    public function testDoNotNeedLogin()
    {
        $transportType = new TransportType();

        $expected = '"milex.transport.mandrill","milex.transport.sendgrid_api","sendmail","milex.transport.sparkpost"';

        $this->assertSame($expected, $transportType->getServiceDoNotNeedUser());
    }

    public function testRequiresPassword()
    {
        $transportType = new TransportType();

        $expected = '"milex.transport.mailjet","milex.transport.sendgrid","milex.transport.pepipost","milex.transport.elasticemail","milex.transport.amazon","milex.transport.amazon_api","milex.transport.postmark","gmail"';

        $this->assertSame($expected, $transportType->getServiceRequiresPassword());
    }

    public function testDoNotNeedPassword()
    {
        $transportType = new TransportType();

        $expected = '"milex.transport.mandrill","milex.transport.sendgrid_api","sendmail","milex.transport.sparkpost"';

        $this->assertSame($expected, $transportType->getServiceDoNotNeedPassword());
    }

    public function testRequiresApiKey()
    {
        $transportType = new TransportType();

        $expected = '"milex.transport.sparkpost","milex.transport.mandrill","milex.transport.sendgrid_api"';

        $this->assertSame($expected, $transportType->getServiceRequiresApiKey());
    }
}
