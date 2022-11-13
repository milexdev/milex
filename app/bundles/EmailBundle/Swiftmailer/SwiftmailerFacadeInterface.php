<?php

namespace Milex\EmailBundle\Swiftmailer;

/**
 * Interface SwiftmailerFacadeInterface.
 */
interface SwiftmailerFacadeInterface
{
    /**
     * @throws \Swift_TransportException
     */
    public function send(\Swift_Mime_SimpleMessage $message);
}
