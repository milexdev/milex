<?php

namespace Milex\EmailBundle\Tests\Helper;

use Milex\CoreBundle\Factory\MilexFactory;
use Milex\EmailBundle\Entity\Email;
use Milex\EmailBundle\Helper\PointEventHelper;
use Milex\EmailBundle\Model\EmailModel;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Model\LeadModel;

class PointEventHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testSendEmail()
    {
        $helper = new PointEventHelper();
        $lead   = new Lead();
        $lead->setFields([
            'core' => [
                'email' => [
                    'value' => 'test@test.com',
                ],
            ],
        ]);
        $event = [
            'id'         => 1,
            'properties' => [
                'email' => 1,
            ],
        ];

        $result = $helper->sendEmail($event, $lead, $this->getMockMilexFactory());
        $this->assertEquals(true, $result);

        $result = $helper->sendEmail($event, $lead, $this->getMockMilexFactory(false));
        $this->assertEquals(false, $result);

        $result = $helper->sendEmail($event, $lead, $this->getMockMilexFactory(true, false));
        $this->assertEquals(false, $result);

        $result = $helper->sendEmail($event, new Lead(), $this->getMockMilexFactory(true, false));
        $this->assertEquals(false, $result);
    }

    /**
     * @param bool $published
     * @param bool $success
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getMockMilexFactory($published = true, $success = true)
    {
        $mock = $this->getMockBuilder(MilexFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getModel'])
            ->getMock()
        ;

        $mock->expects($this->any())
            ->method('getModel')
            ->willReturnCallback(function ($model) use ($published, $success) {
                switch ($model) {
                    case 'email':
                        return $this->getMockEmail($published, $success);
                    case 'lead':
                        return $this->getMockLead();
                }
            });

        return $mock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getMockLead()
    {
        $mock = $this->getMockBuilder(LeadModel::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        return $mock;
    }

    /**
     * @param bool $published
     * @param bool $success
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getMockEmail($published = true, $success = true)
    {
        $sendEmail = $success ? true : ['error' => 1];

        $mock = $this->getMockBuilder(EmailModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntity', 'sendEmail'])
            ->getMock()
        ;

        $mock->expects($this->any())
            ->method('getEntity')
            ->willReturnCallback(function ($id) use ($published) {
                $email = new Email();
                $email->setIsPublished($published);

                return $email;
            });

        $mock->expects($this->any())
            ->method('sendEmail')
            ->willReturn($sendEmail);

        return $mock;
    }
}
