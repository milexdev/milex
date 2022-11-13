<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Tests\Tracker;

use Milex\CoreBundle\Entity\IpAddress;
use Milex\CoreBundle\Helper\CoreParametersHelper;
use Milex\CoreBundle\Helper\IpLookupHelper;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Entity\LeadDevice;
use Milex\LeadBundle\Entity\LeadRepository;
use Milex\LeadBundle\LeadEvents;
use Milex\LeadBundle\Model\FieldModel;
use Milex\LeadBundle\Tracker\ContactTracker;
use Milex\LeadBundle\Tracker\DeviceTracker;
use Milex\LeadBundle\Tracker\Service\ContactTrackingService\ContactTrackingServiceInterface;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ContactTrackerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject|LeadRepository
     */
    private $leadRepositoryMock;

    /**
     * @var MockObject|ContactTrackingServiceInterface
     */
    private $contactTrackingServiceMock;

    /**
     * @var MockObject|DeviceTracker
     */
    private $deviceTrackerMock;

    /**
     * @var MockObject|CorePermissions
     */
    private $securityMock;

    /**
     * @var MockObject|Logger
     */
    private $loggerMock;

    /**
     * @var MockObject|IpLookupHelper
     */
    private $ipLookupHelperMock;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var MockObject|CoreParametersHelper
     */
    private $coreParametersHelperMock;

    /**
     * @var MockObject|EventDispatcher
     */
    private $dispatcherMock;

    /**
     * @var MockObject|FieldModel
     */
    private $leadFieldModelMock;

    protected function setUp(): void
    {
        $this->leadRepositoryMock         = $this->createMock(LeadRepository::class);
        $this->contactTrackingServiceMock = $this->createMock(ContactTrackingServiceInterface::class);
        $this->deviceTrackerMock          = $this->createMock(DeviceTracker::class);
        $this->securityMock               = $this->createMock(CorePermissions::class);
        $this->coreParametersHelperMock   = $this->createMock(CoreParametersHelper::class);
        $this->dispatcherMock             = $this->createMock(EventDispatcher::class);
        $this->leadFieldModelMock         = $this->createMock(FieldModel::class);
        $this->loggerMock                 = $this->createMock(Logger::class);
        $this->ipLookupHelperMock         = $this->createMock(IpLookupHelper::class);
        $this->requestStack               = new RequestStack();

        $this->securityMock->method('isAnonymous')
            ->willReturn(true);

        $this->requestStack->push(new Request());
    }

    public function testSystemContactIsUsedOverTrackedContact(): void
    {
        $contactTracker = $this->getContactTracker();

        $this->leadRepositoryMock->expects($this->any())
            ->method('getFieldValues')
            ->willReturn([]);

        $lead1 = new Lead();
        $lead1->setEmail('lead1@test.com');
        $contactTracker->setTrackedContact($lead1);
        $this->assertEquals($lead1->getEmail(), $contactTracker->getContact()->getEmail());

        $lead2 = new Lead();
        $lead1->setEmail('lead2@test.com');
        $contactTracker->setSystemContact($lead2);
        $this->assertEquals($lead2->getEmail(), $contactTracker->getContact()->getEmail());
    }

    public function testContactIsTrackedByDevice(): void
    {
        $contactTracker = $this->getContactTracker();

        $this->leadRepositoryMock->expects($this->once())
            ->method('getFieldValues')
            ->willReturn(
                [
                    'core' => [
                        'email' => [
                            'alias' => 'email',
                            'type'  => 'email',
                            'value' => 'test@test.com',
                        ],
                    ],
                ]
            );

        $device = new LeadDevice();
        $lead   = new Lead();
        $device->setLead($lead);

        $this->deviceTrackerMock->method('getTrackedDevice')
            ->willReturn($device);

        $contact = $contactTracker->getContact();

        $this->assertEquals('test@test.com', $contact->getFieldValue('email'));
    }

    public function testContactIsTrackedByOldCookie(): void
    {
        $contactTracker = $this->getContactTracker();

        $this->leadRepositoryMock->expects($this->never())
            ->method('getFieldValues');

        $lead = new Lead();
        $lead->setEmail('test@test.com');

        $this->contactTrackingServiceMock->expects($this->once())
            ->method('getTrackedLead')
            ->willReturn($lead);

        $contact = $contactTracker->getContact();

        $this->assertEquals('test@test.com', $contact->getEmail());
    }

    public function testContactIsTrackedByIp(): void
    {
        $contactTracker = $this->getContactTracker();

        $this->ipLookupHelperMock->expects($this->exactly(2))
            ->method('getIpAddress')
            ->willReturn(new IpAddress());

        $this->leadRepositoryMock->expects($this->never())
            ->method('getFieldValues');

        $lead = new Lead();
        $lead->setEmail('test@test.com');

        $this->contactTrackingServiceMock->expects($this->once())
            ->method('getTrackedLead')
            ->willReturn(null);

        $this->coreParametersHelperMock->expects($this->any())
            ->method('get')
            ->willReturn(true);

        $this->leadRepositoryMock->expects($this->once())
            ->method('getLeadsByIp')
            ->willReturn([$lead]);

        $contact = $contactTracker->getContact();

        $this->assertEquals('test@test.com', $contact->getEmail());
    }

    public function testNewContactIsCreated(): void
    {
        $contactTracker = $this->getContactTracker();

        $this->leadRepositoryMock->expects($this->once())
            ->method('getFieldValues')
            ->willReturn([]);

        $this->ipLookupHelperMock->expects($this->exactly(2))
            ->method('getIpAddress')
            ->willReturn(new IpAddress());

        $this->leadRepositoryMock->expects($this->once())
            ->method('getFieldValues');

        $this->contactTrackingServiceMock->expects($this->once())
            ->method('getTrackedLead')
            ->willReturn(null);

        $this->coreParametersHelperMock->expects($this->once())
            ->method('get')
            ->willReturn(false);

        $this->leadRepositoryMock->expects($this->never())
            ->method('getLeadsByIp');
        $this->leadFieldModelMock->expects($this->any())->method('getFieldListWithProperties')->willReturn([]);

        $contact = $contactTracker->getContact();
        $this->assertEquals(true, $contact->isNewlyCreated());
    }

    public function testEventIsDispatchedWithChangeOfContact(): void
    {
        $contactTracker = $this->getContactTracker();

        $device = new LeadDevice();
        $device->setTrackingId('abc123');

        $lead = $this->getMockBuilder(Lead::class)
            ->getMock();
        $lead->method('getId')
            ->willReturn(1);

        $lead2 = $this->getMockBuilder(Lead::class)
            ->getMock();
        $lead2->method('getId')
            ->willReturn(2);

        $this->dispatcherMock->expects($this->once())
            ->method('hasListeners')
            ->withConsecutive([LeadEvents::CURRENT_LEAD_CHANGED])
            ->willReturn(true);

        $this->dispatcherMock->expects($this->once())
            ->method('dispatch')
            ->withConsecutive([LeadEvents::CURRENT_LEAD_CHANGED, $this->anything()])
            ->willReturn(true);

        $leadDevice1 = new LeadDevice();
        $leadDevice2 = new LeadDevice();

        $leadDevice1->setTrackingId('abc123');
        $leadDevice2->setTrackingId('def456');

        $this->deviceTrackerMock->method('getTrackedDevice')
            ->willReturnOnConsecutiveCalls($leadDevice1, $leadDevice2);

        $contactTracker->setTrackedContact($lead);
        $contactTracker->setTrackedContact($lead2);
    }

    private function getContactTracker(): ContactTracker
    {
        return new ContactTracker(
            $this->leadRepositoryMock,
            $this->contactTrackingServiceMock,
            $this->deviceTrackerMock,
            $this->securityMock,
            $this->loggerMock,
            $this->ipLookupHelperMock,
            $this->requestStack,
            $this->coreParametersHelperMock,
            $this->dispatcherMock,
            $this->leadFieldModelMock
        );
    }
}
