<?php

namespace Milex\EmailBundle\Swiftmailer\Momentum\Service;

use Milex\EmailBundle\Swiftmailer\Momentum\DTO\TransmissionDTO;

/**
 * Interface SwiftMessageServiceInterface.
 */
interface SwiftMessageServiceInterface
{
    /**
     * @return TransmissionDTO
     */
    public function transformToTransmission(\Swift_Mime_SimpleMessage $message);
}
