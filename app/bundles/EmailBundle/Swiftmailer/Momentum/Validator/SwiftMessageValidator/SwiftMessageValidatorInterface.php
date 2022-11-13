<?php

namespace Milex\EmailBundle\Swiftmailer\Momentum\Validator\SwiftMessageValidator;

use Milex\EmailBundle\Swiftmailer\Momentum\Exception\Validator\SwiftMessageValidator\SwiftMessageValidationException;

/**
 * Interface SwiftMessageValidatorInterface.
 */
interface SwiftMessageValidatorInterface
{
    /**
     * @throws SwiftMessageValidationException
     */
    public function validate(\Swift_Mime_SimpleMessage $message);
}
