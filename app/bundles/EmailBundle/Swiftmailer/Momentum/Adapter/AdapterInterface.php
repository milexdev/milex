<?php

namespace Milex\EmailBundle\Swiftmailer\Momentum\Adapter;

use Milex\EmailBundle\Swiftmailer\Momentum\DTO\TransmissionDTO;
use SparkPost\SparkPostPromise;

/**
 * Interface AdapterInterface.
 */
interface AdapterInterface
{
    /**
     * @return SparkPostPromise
     */
    public function createTransmission(TransmissionDTO $transmissionDTO);
}
