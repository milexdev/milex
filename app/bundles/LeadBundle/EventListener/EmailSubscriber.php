<?php

namespace Milex\LeadBundle\EventListener;

use Milex\CoreBundle\Event\TokenReplacementEvent;
use Milex\CoreBundle\Helper\BuilderTokenHelperFactory;
use Milex\EmailBundle\EmailEvents;
use Milex\EmailBundle\Event\EmailBuilderEvent;
use Milex\EmailBundle\Event\EmailSendEvent;
use Milex\LeadBundle\Helper\TokenHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EmailSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private static $contactFieldRegex = '{contactfield=(.*?)}';

    /**
     * @var BuilderTokenHelperFactory
     */
    private $builderTokenHelperFactory;

    public function __construct(BuilderTokenHelperFactory $builderTokenHelperFactory)
    {
        $this->builderTokenHelperFactory = $builderTokenHelperFactory;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            EmailEvents::EMAIL_ON_BUILD                     => ['onEmailBuild', 0],
            EmailEvents::EMAIL_ON_SEND                      => ['onEmailGenerate', 0],
            EmailEvents::EMAIL_ON_DISPLAY                   => ['onEmailDisplay', 0],
            EmailEvents::ON_EMAIL_ADDRESS_TOKEN_REPLACEMENT => ['onEmailAddressReplacement', 0],
        ];
    }

    public function onEmailBuild(EmailBuilderEvent $event)
    {
        $tokenHelper = $this->builderTokenHelperFactory->getBuilderTokenHelper('lead.field', 'lead:fields', 'MilexLeadBundle');
        // the permissions are for viewing contact data, not for managing contact fields
        $tokenHelper->setPermissionSet(['lead:leads:viewown', 'lead:leads:viewother']);

        if ($event->tokensRequested(self::$contactFieldRegex)) {
            $event->addTokensFromHelper($tokenHelper, self::$contactFieldRegex, 'label', 'alias');
        }
    }

    public function onEmailDisplay(EmailSendEvent $event)
    {
        $this->onEmailGenerate($event);
    }

    public function onEmailGenerate(EmailSendEvent $event)
    {
        // Combine all possible content to find tokens across them
        $content = $event->getSubject();
        $content .= $event->getContent();
        $content .= $event->getPlainText();
        $content .= implode(' ', $event->getTextHeaders());

        $lead = $event->getLead();

        $tokenList = TokenHelper::findLeadTokens($content, $lead);
        if (count($tokenList)) {
            $event->addTokens($tokenList);
            unset($tokenList);
        }
    }

    public function onEmailAddressReplacement(TokenReplacementEvent $event): void
    {
        $event->setContent(TokenHelper::findLeadTokens($event->getContent(), $event->getLead()->getProfileFields(), true));
    }
}
