<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Tests\Tracker\Service\ContactTrackingService;

use Milex\CoreBundle\Helper\CookieHelper;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Entity\LeadDeviceRepository;
use Milex\LeadBundle\Entity\LeadRepository;
use Milex\LeadBundle\Entity\MergeRecordRepository;
use Milex\LeadBundle\Tracker\Service\ContactTrackingService\ContactTrackingService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ContactTrackingServiceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject|CookieHelper
     */
    private $cookieHelperMock;

    /**
     * @var MockObject|LeadDeviceRepository
     */
    private $leadDeviceRepositoryMock;

    /**
     * @var MockObject|LeadRepository
     */
    private $leadRepositoryMock;

    /**
     * @var MockObject|RequestStack
     */
    private $requestStackMock;

    /**
     * @var MockObject|MergeRecordRepository
     */
    private $mergeRecordRepository;

    protected function setUp(): void
    {
        $this->cookieHelperMock         = $this->createMock(CookieHelper::class);
        $this->leadDeviceRepositoryMock = $this->createMock(LeadDeviceRepository::class);
        $this->leadRepositoryMock       = $this->createMock(LeadRepository::class);
        $this->requestStackMock         = $this->createMock(RequestStack::class);
        $this->mergeRecordRepository    = $this->createMock(MergeRecordRepository::class);
    }

    public function testGetTrackedIdentifier(): void
    {
        $trackingId = 'randomTrackingId';

        $this->cookieHelperMock->expects($this->once())
            ->method('getCookie')
            ->with('milex_session_id', null)
            ->willReturn($trackingId);

        $contactTrackingService = $this->getContactTrackingService();
        $this->assertSame($trackingId, $contactTrackingService->getTrackedIdentifier());
    }

    public function testGetTrackedLeadNoRequest(): void
    {
        $this->requestStackMock->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $contactTrackingService = $this->getContactTrackingService();
        $this->assertNull($contactTrackingService->getTrackedLead());
    }

    public function testGetTrackedLeadNoTrackedIdentifier(): void
    {
        $requestMock = $this->createMock(Request::class);

        $this->requestStackMock->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($requestMock);

        $this->cookieHelperMock->expects($this->once())
            ->method('getCookie')
            ->with('milex_session_id', null)
            ->willReturn(null);

        $contactTrackingService = $this->getContactTrackingService();
        $this->assertNull($contactTrackingService->getTrackedLead());
    }

    /**
     * Test no lead id found.
     */
    public function testGetTrackedLeadNoLeadId(): void
    {
        $requestMock = $this->createMock(Request::class);
        $trackingId  = 'randomTrackingId';

        $this->requestStackMock->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($requestMock);

        $this->cookieHelperMock->expects($this->exactly(2))
            ->method('getCookie')
            ->withConsecutive(
                ['milex_session_id', null],
                [$trackingId, null]
            )
            ->willReturnOnConsecutiveCalls($trackingId, null);

        $requestMock->expects($this->once())
            ->method('get')
            ->with('mtc_id', null)
            ->willReturn(null);

        $contactTrackingService = $this->getContactTrackingService();
        $this->assertNull($contactTrackingService->getTrackedLead());
    }

    /**
     * Test lead id found in request but no lead entity found.
     */
    public function testGetTrackedLeadRequestLeadIdAndNoLeadFound(): void
    {
        $requestMock = $this->createMock(Request::class);
        $trackingId  = 'randomTrackingId';
        $leadId      = 1;

        $this->requestStackMock->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($requestMock);

        $this->cookieHelperMock->expects($this->exactly(2))
            ->method('getCookie')
            ->withConsecutive(
                ['milex_session_id', null],
                [$trackingId, null]
            )
            ->willReturnOnConsecutiveCalls($trackingId, null);

        $requestMock->expects($this->once())
            ->method('get')
            ->with('mtc_id', null)
            ->willReturn($leadId);

        $this->leadRepositoryMock->expects($this->once())
            ->method('getEntity')
            ->with($leadId)
            ->willReturn(null);

        $contactTrackingService = $this->getContactTrackingService();
        $this->assertNull($contactTrackingService->getTrackedLead());
    }

    /**
     * Test lead id found in request and another device is already tracked and associated with lead.
     */
    public function testGetTrackedLeadRequestLeadIdAndAnotherDeviceAlreadyTracked(): void
    {
        $requestMock = $this->createMock(Request::class);
        $trackingId  = 'randomTrackingId';
        $leadId      = 1;
        $leadMock    = $this->createMock(Lead::class);

        $this->requestStackMock->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($requestMock);

        $this->cookieHelperMock->expects($this->exactly(2))
            ->method('getCookie')
            ->withConsecutive(
                ['milex_session_id', null],
                [$trackingId, null]
            )
            ->willReturnOnConsecutiveCalls($trackingId, null);

        $requestMock->expects($this->once())
            ->method('get')
            ->with('mtc_id', null)
            ->willReturn($leadId);

        $this->leadRepositoryMock->expects($this->once())
            ->method('getEntity')
            ->with($leadId)
            ->willReturn($leadMock);

        $this->leadDeviceRepositoryMock->expects($this->once())
            ->method('isAnyLeadDeviceTracked')
            ->with($leadMock)
            ->willReturn(true);

        $contactTrackingService = $this->getContactTrackingService();
        $this->assertNull($contactTrackingService->getTrackedLead());
    }

    /**
     * Test lead id found in request and another device is not tracked and associated with lead.
     */
    public function testGetTrackedLeadRequestLeadIdAndAnotherDeviceNotTracked(): void
    {
        $requestMock = $this->createMock(Request::class);
        $trackingId  = 'randomTrackingId';
        $leadId      = 1;
        $leadMock    = $this->createMock(Lead::class);

        $this->requestStackMock->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($requestMock);

        $this->cookieHelperMock->expects($this->exactly(2))
            ->method('getCookie')
            ->withConsecutive(
                ['milex_session_id', null],
                [$trackingId, null]
            )
            ->willReturnOnConsecutiveCalls($trackingId, null);

        $requestMock->expects($this->once())
            ->method('get')
            ->with('mtc_id', null)
            ->willReturn($leadId);

        $this->leadRepositoryMock->expects($this->once())
            ->method('getEntity')
            ->with($leadId)
            ->willReturn($leadMock);

        $this->leadDeviceRepositoryMock->expects($this->once())
            ->method('isAnyLeadDeviceTracked')
            ->with($leadMock)
            ->willReturn(false);

        $contactTrackingService = $this->getContactTrackingService();
        $this->assertSame($leadMock, $contactTrackingService->getTrackedLead());
    }

    /**
     * Test lead id found in request and another device is not tracked and associated with lead.
     */
    public function testGetTrackedLeadCookieLeadIdAndAnotherDeviceNotTracked(): void
    {
        $requestMock = $this->createMock(Request::class);
        $trackingId  = 'randomTrackingId';
        $leadId      = 1;
        $leadMock    = $this->createMock(Lead::class);

        $this->requestStackMock->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($requestMock);

        $this->cookieHelperMock->expects($this->exactly(2))
            ->method('getCookie')
            ->withConsecutive(
                ['milex_session_id', null],
                [$trackingId, null]
            )
            ->willReturnOnConsecutiveCalls($trackingId, $leadId);

        $this->leadRepositoryMock->expects($this->once())
            ->method('getEntity')
            ->with($leadId)
            ->willReturn($leadMock);

        $this->leadDeviceRepositoryMock->expects($this->once())
            ->method('isAnyLeadDeviceTracked')
            ->with($leadMock)
            ->willReturn(false);

        $contactTrackingService = $this->getContactTrackingService();
        $this->assertSame($leadMock, $contactTrackingService->getTrackedLead());
    }

    private function getContactTrackingService(): ContactTrackingService
    {
        return new ContactTrackingService(
            $this->cookieHelperMock,
            $this->leadDeviceRepositoryMock,
            $this->leadRepositoryMock,
            $this->mergeRecordRepository,
            $this->requestStackMock
        );
    }
}
