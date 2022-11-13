<?php

namespace Milex\EmailBundle\Tests\Model;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\Factory\MilexFactory;
use Milex\CoreBundle\Translation\Translator;
use Milex\EmailBundle\Entity\CopyRepository;
use Milex\EmailBundle\Entity\Email;
use Milex\EmailBundle\Entity\Stat;
use Milex\EmailBundle\Entity\StatRepository;
use Milex\EmailBundle\Event\EmailSendEvent;
use Milex\EmailBundle\Exception\FailedToSendToContactException;
use Milex\EmailBundle\Helper\MailHelper;
use Milex\EmailBundle\Model\EmailModel;
use Milex\EmailBundle\Model\SendEmailToContact;
use Milex\EmailBundle\Stat\StatHelper;
use Milex\EmailBundle\Swiftmailer\Exception\BatchQueueMaxException;
use Milex\EmailBundle\Tests\Helper\Transport\BatchTransport;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Model\DoNotContact;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Routing\Router;

class SendEmailToContactTest extends \PHPUnit\Framework\TestCase
{
    protected $contacts = [
        [
            'id'        => 1,
            'email'     => 'contact1@somewhere.com',
            'firstname' => 'Contact',
            'lastname'  => '1',
            'owner_id'  => 1,
        ],
        [
            'id'        => 2,
            'email'     => 'contact2@somewhere.com',
            'firstname' => 'Contact',
            'lastname'  => '2',
            'owner_id'  => 0,
        ],
        [
            'id'        => 3,
            'email'     => 'contact3@somewhere.com',
            'firstname' => 'Contact',
            'lastname'  => '3',
            'owner_id'  => 2,
        ],
        [
            'id'        => 4,
            'email'     => 'contact4@somewhere.com',
            'firstname' => 'Contact',
            'lastname'  => '4',
            'owner_id'  => 1,
        ],
    ];

    /**
     * @testdox Tests that all contacts are temporarily failed if an Email entity happens to be incorrectly configured
     *
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::setEmail()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::setContact()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::send()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::finalFlush()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::failContact()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::getFailedContacts()
     */
    public function testContactsAreFailedIfSettingEmailEntityFails()
    {
        $mailHelper = $this->getMockBuilder(MailHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mailHelper->method('setEmail')
            ->willReturn(false);

        $statRepository = $this->getMockBuilder(StatRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dncModel = $this->getMockBuilder(DoNotContact::class)
            ->disableOriginalConstructor()
            ->getMock();

        // This should not be called because contact emails are just fine; the problem is with the email entity
        $dncModel->expects($this->never())
            ->method('addDncForContact');

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statHelper = new StatHelper($statRepository);

        $model = new SendEmailToContact($mailHelper, $statHelper, $dncModel, $translator);

        $email = new Email();
        $model->setEmail($email);

        foreach ($this->contacts as $contact) {
            try {
                $model->setContact($contact)
                    ->send();
            } catch (FailedToSendToContactException $exception) {
            }
        }

        $model->finalFlush();

        $failedContacts = $model->getFailedContacts();

        $this->assertCount(4, $failedContacts);
    }

    /**
     * @testdox Tests that bad emails are failed
     *
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::setContact()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::send()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::finalFlush()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::failContact()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::getFailedContacts()
     */
    public function testExceptionIsThrownIfEmailIsSentToBadContact()
    {
        $emailMock = $this->getMockBuilder(Email::class)
            ->getMock();
        $emailMock
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $mailHelper = $this->getMockBuilder(MailHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mailHelper->method('setEmail')
            ->willReturn(true);
        $mailHelper->method('addTo')
            ->willReturnCallback(
                function ($email) {
                    return '@bad.com' !== $email;
                }
            );
        $mailHelper->method('queue')
            ->willReturn([true, []]);

        $stat = new Stat();
        $stat->setEmail($emailMock);
        $mailHelper->method('createEmailStat')
            ->willReturn($stat);

        $statRepository = $this->getMockBuilder(StatRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dncModel = $this->getMockBuilder(DoNotContact::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dncModel->expects($this->once())
            ->method('addDncForContact');

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statHelper = new StatHelper($statRepository);

        $model = new SendEmailToContact($mailHelper, $statHelper, $dncModel, $translator);
        $model->setEmail($emailMock);

        $contacts             = $this->contacts;
        $contacts[0]['email'] = '@bad.com';

        $exceptionThrown = false;
        foreach ($contacts as $contact) {
            try {
                $model->setContact($contact)
                    ->send();
            } catch (FailedToSendToContactException $exception) {
                $exceptionThrown = true;
            }
        }

        if (!$exceptionThrown) {
            $this->fail('FailedToSendToContactException not thrown');
        }

        $model->finalFlush();

        $failedContacts = $model->getFailedContacts();

        $this->assertCount(1, $failedContacts);
    }

    /**
     * @testdox Test a tokenized transport that limits batches does not throw BatchQueueMaxException on subsequent contacts when one fails
     *
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::setContact()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::send()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::failContact()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::getFailedContacts()
     */
    public function testBadEmailDoesNotCauseBatchQueueMaxExceptionOnSubsequentContacts()
    {
        defined('MILEX_ENV') or define('MILEX_ENV', 'test');

        $emailMock = $this->getMockBuilder(Email::class)
            ->getMock();
        $emailMock
            ->method('getId')
            ->will($this->returnValue(1));
        $emailMock->method('getFromAddress')
            ->willReturn('test@milex.com');

        // Use our test token transport limiting to 1 recipient per queue
        $transport = new BatchTransport(false, 1);
        $mailer    = new \Swift_Mailer($transport);

        // Mock factory to ensure that queue mode is handled until MailHelper is refactored completely away from MilexFactory
        $factoryMock = $this->getMockBuilder(MilexFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock->method('getParameter')
            ->willReturnCallback(
                function ($param) {
                    switch ($param) {
                        case 'mailer_spool_type':
                            return 'memory';
                        default:
                            return '';
                    }
                }
            );
        $factoryMock->method('getLogger')
            ->willReturn(
                new NullLogger()
            );
        $factoryMock->method('getDispatcher')
            ->willReturn(
                new EventDispatcher()
            );
        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock->method('getRouter')
            ->willReturn($routerMock);

        $mailHelper = $this->getMockBuilder(MailHelper::class)
            ->setConstructorArgs([$factoryMock, $mailer])
            ->onlyMethods(['createEmailStat'])
            ->getMock();

        $mailHelper->method('createEmailStat')
            ->willReturnCallback(
                function () use ($emailMock) {
                    $stat = new Stat();
                    $stat->setEmail($emailMock);

                    $leadMock = $this->getMockBuilder(Lead::class)
                        ->getMock();
                    $leadMock->method('getId')
                        ->willReturn(1);

                    $stat->setLead($leadMock);

                    return $stat;
                }
            );

        // Enable queueing
        $mailHelper->enableQueue();

        $statRepository = $this->getMockBuilder(StatRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dncModel = $this->getMockBuilder(DoNotContact::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dncModel->expects($this->exactly(1))
            ->method('addDncForContact');

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statHelper = new StatHelper($statRepository);

        $model = new SendEmailToContact($mailHelper, $statHelper, $dncModel, $translator);
        $model->setEmail($emailMock);

        $contacts             = $this->contacts;
        $contacts[0]['email'] = '@bad.com';

        foreach ($contacts as $contact) {
            try {
                $model->setContact($contact)
                    ->send();
            } catch (FailedToSendToContactException $exception) {
                // We're good here
            } catch (BatchQueueMaxException $exception) {
                $this->fail('BatchQueueMaxException thrown');
            }
        }

        $model->finalFlush();

        $failedContacts = $model->getFailedContacts();

        $this->assertCount(1, $failedContacts);

        // Our fake transport should have processed 3 metadatas
        $this->assertCount(3, $transport->getMetadatas());

        // We made it this far so all of the emails were processed despite a bad email in the batch
    }

    /**
     * @testdox Test a tokenized transport that fills tokens correctly
     *
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::setContact()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::send()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::failContact()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::getFailedContacts()
     */
    public function testBatchQueueContactsHaveTokensHydrated()
    {
        defined('MILEX_ENV') or define('MILEX_ENV', 'test');

        $emailMock = $this->getMockBuilder(Email::class)
            ->getMock();
        $emailMock
            ->method('getId')
            ->will($this->returnValue(1));
        $emailMock->method('getFromAddress')
            ->willReturn('test@milex.com');
        $emailMock->method('getCustomHtml')
            ->willReturn('Hi {contactfield=firstname}');

        // Use our test token transport limiting to 1 recipient per queue
        $transport = new BatchTransport(false, 1);
        $mailer    = new \Swift_Mailer($transport);

        // Mock factory to ensure that queue mode is handled until MailHelper is refactored completely away from MilexFactory
        $factoryMock = $this->getMockBuilder(MilexFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock->method('getParameter')
            ->willReturnCallback(
                function ($param) {
                    switch ($param) {
                        case 'mailer_spool_type':
                            return 'memory';
                        default:
                            return '';
                    }
                }
            );
        $factoryMock->method('getLogger')
            ->willReturn(
                new NullLogger()
            );

        $mockEm = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock->method('getEntityManager')
            ->willReturn($mockEm);

        $mockDispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->getMock();
        $mockDispatcher->method('dispatch')
            ->willReturnCallback(
                function ($eventName, EmailSendEvent $event) {
                    $lead = $event->getLead();

                    $tokens = [];
                    foreach ($lead as $field => $value) {
                        $tokens["{contactfield=$field}"] = $value;
                    }
                    $tokens['{hash}'] = $event->getIdHash();

                    $event->addTokens($tokens);
                }
            );
        $factoryMock->method('getDispatcher')
            ->willReturn($mockDispatcher);
        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock->method('getRouter')
            ->willReturn($routerMock);

        $copyRepoMock = $this->getMockBuilder(CopyRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailModelMock = $this->getMockBuilder(EmailModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailModelMock->method('getCopyRepository')
            ->willReturn($copyRepoMock);

        $factoryMock->method('getModel')
            ->willReturn($emailModelMock);

        $mailHelper = $this->getMockBuilder(MailHelper::class)
            ->setConstructorArgs([$factoryMock, $mailer])
            ->onlyMethods([])
            ->getMock();

        // Enable queueing
        $mailHelper->enableQueue();

        $statRepository = $this->getMockBuilder(StatRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statRepository->method('saveEntity')
            ->willReturnCallback(
                function (Stat $stat) {
                    $tokens = $stat->getTokens();
                    $this->assertGreaterThan(1, count($tokens));
                    $this->assertEquals($stat->getTrackingHash(), $tokens['{hash}']);
                }
            );

        $dncModel = $this->getMockBuilder(DoNotContact::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statHelper = new StatHelper($statRepository);

        $model = new SendEmailToContact($mailHelper, $statHelper, $dncModel, $translator);
        $model->setEmail($emailMock);

        foreach ($this->contacts as $contact) {
            try {
                $model->setContact($contact)
                    ->send();
            } catch (FailedToSendToContactException $exception) {
                // We're good here
            } catch (BatchQueueMaxException $exception) {
                $this->fail('BatchQueueMaxException thrown');
            }
        }

        $model->finalFlush();

        $this->assertCount(4, $transport->getMetadatas());
    }

    /**
     * @testdox Test that stat entries are saved in batches of 20
     *
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::setContact()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::send()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::failContact()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::createContactStatEntry()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::getFailedContacts()
     */
    public function testThatStatEntriesAreCreatedAndPersistedEveryBatch()
    {
        defined('MILEX_ENV') or define('MILEX_ENV', 'test');

        $emailMock = $this->getMockBuilder(Email::class)
            ->getMock();
        $emailMock
            ->method('getId')
            ->will($this->returnValue(1));
        $emailMock->method('getFromAddress')
            ->willReturn('test@milex.com');

        // Use our test token transport limiting to 1 recipient per queue
        $transport = new BatchTransport(false, 1);
        $mailer    = new \Swift_Mailer($transport);

        // Mock factory to ensure that queue mode is handled until MailHelper is refactored completely away from MilexFactory
        $factoryMock = $this->getMockBuilder(MilexFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock->method('getParameter')
            ->willReturnCallback(
                function ($param) {
                    switch ($param) {
                        case 'mailer_spool_type':
                            return 'memory';
                        default:
                            return '';
                    }
                }
            );
        $factoryMock->method('getLogger')
            ->willReturn(
                new NullLogger()
            );
        $factoryMock->method('getDispatcher')
            ->willReturn(
                new EventDispatcher()
            );
        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock->method('getRouter')
            ->willReturn($routerMock);

        $mailHelper = $this->getMockBuilder(MailHelper::class)
            ->setConstructorArgs([$factoryMock, $mailer])
            ->onlyMethods(['createEmailStat'])
            ->getMock();

        $mailHelper->expects($this->exactly(21))
            ->method('createEmailStat')
            ->willReturnCallback(
                function () use ($emailMock) {
                    $stat = new Stat();
                    $stat->setEmail($emailMock);

                    $leadMock = $this->getMockBuilder(Lead::class)
                        ->getMock();
                    $leadMock->method('getId')
                        ->willReturn(1);

                    $stat->setLead($leadMock);

                    return $stat;
                }
            );

        // Enable queueing
        $mailHelper->enableQueue();

        // Here's the test; this should be called after 20 contacts are processed
        $statRepository = $this->getMockBuilder(StatRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statRepository->expects($this->exactly(21))
            ->method('saveEntity');

        $dncModel = $this->getMockBuilder(DoNotContact::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dncModel->expects($this->never())
            ->method('addDncForContact');

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statHelper = new StatHelper($statRepository);

        $model = new SendEmailToContact($mailHelper, $statHelper, $dncModel, $translator);
        $model->setEmail($emailMock);

        // Let's generate 20 bogus contacts
        $contacts = [];
        $counter  = 0;
        while ($counter <= 20) {
            $contacts[] = [
                'id'        => $counter,
                'email'     => 'email'.uniqid().'@somewhere.com',
                'firstname' => 'Contact',
                'lastname'  => uniqid(),
            ];

            ++$counter;
        }

        foreach ($contacts as $contact) {
            try {
                $model->setContact($contact)
                    ->send();
            } catch (FailedToSendToContactException $exception) {
                $this->fail('FailedToSendToContactException thrown: '.$exception->getMessage());
            } catch (BatchQueueMaxException $exception) {
                $this->fail('BatchQueueMaxException thrown: '.$exception->getMessage());
            }
        }

        $model->finalFlush();

        $failedContacts = $model->getFailedContacts();
        $this->assertCount(0, $failedContacts);
        $this->assertCount(21, $transport->getMetadatas());
    }

    /**
     * @testdox Test that a failed email from the transport is handled
     *
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::setContact()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::send()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::failContact()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::getFailedContacts()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::upEmailSentCount()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::downEmailSentCount()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::getSentCounts()
     */
    public function testThatAFailureFromTransportIsHandled()
    {
        defined('MILEX_ENV') or define('MILEX_ENV', 'test');

        $emailMock = $this->getMockBuilder(Email::class)
            ->getMock();
        $emailMock
            ->method('getId')
            ->will($this->returnValue(1));
        $emailMock->method('getFromAddress')
            ->willReturn('test@milex.com');

        // Use our test token transport limiting to 1 recipient per queue
        $transport = new BatchTransport(true, 1);
        $mailer    = new \Swift_Mailer($transport);

        // Mock factory to ensure that queue mode is handled until MailHelper is refactored completely away from MilexFactory
        $factoryMock = $this->getMockBuilder(MilexFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock->method('getParameter')
            ->willReturnCallback(
                function ($param) {
                    switch ($param) {
                        case 'mailer_spool_type':
                            return 'memory';
                        default:
                            return '';
                    }
                }
            );
        $factoryMock->method('getLogger')
            ->willReturn(
                new NullLogger()
            );
        $factoryMock->method('getDispatcher')
            ->willReturn(
                new EventDispatcher()
            );
        $routerMock = $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factoryMock->method('getRouter')
            ->willReturn($routerMock);

        $mailHelper = $this->getMockBuilder(MailHelper::class)
            ->setConstructorArgs([$factoryMock, $mailer])
            ->onlyMethods(['createEmailStat'])
            ->getMock();

        $mailHelper->method('createEmailStat')
            ->willReturnCallback(
                function () use ($emailMock) {
                    $stat = new Stat();
                    $stat->setEmail($emailMock);

                    $leadMock = $this->getMockBuilder(Lead::class)
                        ->getMock();
                    $leadMock->method('getId')
                        ->willReturn(1);

                    $stat->setLead($leadMock);

                    return $stat;
                }
            );

        // Enable queueing
        $mailHelper->enableQueue();

        $statRepository = $this->getMockBuilder(StatRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dncModel = $this->getMockBuilder(DoNotContact::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dncModel->expects($this->never())
            ->method('addDncForContact');

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statHelper = new StatHelper($statRepository);

        $model = new SendEmailToContact($mailHelper, $statHelper, $dncModel, $translator);
        $model->setEmail($emailMock);

        foreach ($this->contacts as $contact) {
            try {
                $model->setContact($contact)
                    ->send();
            } catch (FailedToSendToContactException $exception) {
                // We're good here
            } catch (BatchQueueMaxException $exception) {
                $this->fail('BatchQueueMaxException thrown');
            }
        }

        $model->finalFlush();

        $failedContacts = $model->getFailedContacts();

        $this->assertCount(1, $failedContacts);

        $counts = $model->getSentCounts();

        // Should have increased to 4, one failed via the transport so back down to 3
        $this->assertEquals(3, $counts[1]);

        // One error message from the transport
        $errorMessages = $model->getErrors();
        $this->assertCount(1, $errorMessages);
    }

    /**
     * @testdox Test that sending an email with invalid Bcc address is handled
     *
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::setContact()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::send()
     * @covers \Milex\EmailBundle\Model\SendEmailToContact::failContact()
     */
    public function testThatInvalidBccFailureIsHandled()
    {
        defined('MILEX_ENV') or define('MILEX_ENV', 'test');

        $mockFactory = $this->getMockBuilder(MilexFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockFactory->method('getParameter')
            ->will(
                $this->returnValueMap(
                    [
                        ['mailer_return_path', false, null],
                        ['mailer_spool_type', false, 'memory'],
                    ]
                )
            );
        $mockFactory->method('getLogger')
            ->willReturn(
                new NullLogger()
            );

        $swiftMailer = new \Swift_Mailer(new BatchTransport());

        $mailHelper = new MailHelper($mockFactory, $swiftMailer, ['nobody@nowhere.com' => 'No Body']);

        $statRepository = $this->getMockBuilder(StatRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dncModel = $this->getMockBuilder(DoNotContact::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $statHelper = new StatHelper($statRepository);

        $model = new SendEmailToContact($mailHelper, $statHelper, $dncModel, $translator);

        $emailMock = $this->getMockBuilder(Email::class)
            ->getMock();
        $emailMock
            ->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        // Set invalid BCC (should use comma as separator)
        $emailMock
            ->expects($this->any())
            ->method('getBccAddress')
            ->willReturn('test@milex.com; test@milex.com');

        $model->setEmail($emailMock);

        $stat = new Stat();
        $stat->setEmail($emailMock);

        $this->expectException(FailedToSendToContactException::class);
        $this->expectExceptionMessage('Address in mailbox given [test@milex.com; test@milex.com] does not comply with RFC 2822, 3.6.2.');

        // Send should trigger the FailedToSendToContactException
        $model->setContact($this->contacts[0])->send();
    }
}
