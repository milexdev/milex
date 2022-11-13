<?php

namespace Milex\LeadBundle\Tracker\Service\DeviceCreatorService;

use DeviceDetector\DeviceDetector;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Entity\LeadDevice;

/**
 * Class DeviceCreatorService.
 */
final class DeviceCreatorService implements DeviceCreatorServiceInterface
{
    /**
     * @return LeadDevice|null Null is returned if device can't be detected
     */
    public function getCurrentFromDetector(DeviceDetector $deviceDetector, Lead $assignedLead)
    {
        $device = new LeadDevice();
        $device->setClientInfo($deviceDetector->getClient());
        $device->setDevice($deviceDetector->getDeviceName());
        $device->setDeviceBrand($deviceDetector->getBrandName());
        $device->setDeviceModel($deviceDetector->getModel());
        $device->setDeviceOs($deviceDetector->getOs());
        $device->setDateAdded(new \DateTime());
        $device->setLead($assignedLead);

        return $device;
    }
}
