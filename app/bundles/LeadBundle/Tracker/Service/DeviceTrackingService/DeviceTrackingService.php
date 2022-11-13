<?php

namespace Milex\LeadBundle\Tracker\Service\DeviceTrackingService;

use Doctrine\ORM\EntityManagerInterface;
use Milex\CoreBundle\Helper\CookieHelper;
use Milex\CoreBundle\Helper\RandomHelper\RandomHelperInterface;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\LeadBundle\Entity\LeadDevice;
use Milex\LeadBundle\Entity\LeadDeviceRepository;
use Symfony\Component\HttpFoundation\RequestStack;

final class DeviceTrackingService implements DeviceTrackingServiceInterface
{
    /**
     * @var CookieHelper
     */
    private $cookieHelper;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LeadDeviceRepository
     */
    private $leadDeviceRepository;

    /**
     * @var RandomHelperInterface
     */
    private $randomHelper;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var LeadDevice
     */
    private $trackedDevice;

    /**
     * @var CorePermissions
     */
    private $security;

    public function __construct(
        CookieHelper $cookieHelper,
        EntityManagerInterface $entityManager,
        LeadDeviceRepository $leadDeviceRepository,
        RandomHelperInterface $randomHelper,
        RequestStack $requestStack,
        CorePermissions $security
    ) {
        $this->cookieHelper         = $cookieHelper;
        $this->entityManager        = $entityManager;
        $this->randomHelper         = $randomHelper;
        $this->leadDeviceRepository = $leadDeviceRepository;
        $this->requestStack         = $requestStack;
        $this->security             = $security;
    }

    /**
     * @return bool
     */
    public function isTracked()
    {
        return null !== $this->getTrackedDevice();
    }

    /**
     * @return LeadDevice
     */
    public function getTrackedDevice()
    {
        if (!$this->security->isAnonymous()) {
            // Do not track Milex users
            return;
        }

        if ($this->trackedDevice) {
            return $this->trackedDevice;
        }

        $trackingId = $this->getTrackedIdentifier();
        if (null === $trackingId) {
            return null;
        }

        return $this->leadDeviceRepository->getByTrackingId($trackingId);
    }

    /**
     * @param bool $replaceExistingTracking
     *
     * @return LeadDevice
     */
    public function trackCurrentDevice(LeadDevice $device, $replaceExistingTracking = false)
    {
        $trackedDevice = $this->getTrackedDevice();
        if (null !== $trackedDevice && false === $replaceExistingTracking) {
            return $trackedDevice;
        }

        // Check for an existing device for this contact to prevent blowing up the devices table
        $existingDevice = $this->leadDeviceRepository->findOneBy(
            [
                'lead'        => $device->getLead(),
                'device'      => $device->getDevice(),
                'deviceBrand' => $device->getDeviceBrand(),
                'deviceModel' => $device->getDeviceModel(),
            ]
        );

        if (null !== $existingDevice) {
            $device = $existingDevice;
        }

        if (null === $device->getTrackingId()) {
            // Ensure all devices have a tracking ID (new devices will not and pre 2.13.0 devices may not)
            $device->setTrackingId($this->getUniqueTrackingIdentifier());

            $this->entityManager->persist($device);
            $this->entityManager->flush();
        }

        $this->createTrackingCookies($device);

        // Store the device in case a service uses this within the same session
        $this->trackedDevice = $device;

        return $device;
    }

    public function clearTrackingCookies()
    {
        $this->cookieHelper->deleteCookie('milex_device_id');
        $this->cookieHelper->deleteCookie('mtc_id');
        $this->cookieHelper->deleteCookie('mtc_sid');
    }

    private function getTrackedIdentifier(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return null;
        }

        if ($this->trackedDevice) {
            // Use the device tracked in case the cookies were just created
            return $this->trackedDevice->getTrackingId();
        }

        $deviceTrackingId = $this->cookieHelper->getCookie('milex_device_id', null);
        if (null === $deviceTrackingId) {
            $deviceTrackingId = $request->get('milex_device_id', null);
        }

        return $deviceTrackingId;
    }

    private function getUniqueTrackingIdentifier(): string
    {
        do {
            $generatedIdentifier = $this->randomHelper->generate(23);
            $device              = $this->leadDeviceRepository->getByTrackingId($generatedIdentifier);
        } while (null !== $device);

        return $generatedIdentifier;
    }

    private function createTrackingCookies(LeadDevice $device)
    {
        // Device cookie
        $this->cookieHelper->setCookie('milex_device_id', $device->getTrackingId(), 31536000);

        // Mainly for landing pages so that JS has the same access as 3rd party tracking code
        $this->cookieHelper->setCookie('mtc_id', $device->getLead()->getId(), null);
        $this->cookieHelper->setCookie('mtc_sid', $device->getTrackingId(), null);
    }
}
