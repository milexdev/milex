<?php

namespace Milex\EmailBundle\Tests\Helper\EventListener;

use Milex\EmailBundle\EmailEvents;
use Milex\EmailBundle\Event\EmailValidationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EmailValidationSubscriber.
 */
class EmailValidationSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::ON_EMAIL_VALIDATION => ['onEmailValidation', 0],
        ];
    }

    public function onEmailValidation(EmailValidationEvent $event)
    {
        if ('bad@gmail.com' === $event->getAddress()) {
            $event->setInvalid('bad email');
        } // defaults to valid
    }
}
