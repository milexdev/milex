<?php

namespace Milex\EmailBundle\MonitoredEmail\Processor;

use Milex\EmailBundle\MonitoredEmail\Exception\FeedbackLoopNotFound;
use Milex\EmailBundle\MonitoredEmail\Message;
use Milex\EmailBundle\MonitoredEmail\Processor\FeedbackLoop\Parser;
use Milex\EmailBundle\MonitoredEmail\Search\ContactFinder;
use Milex\LeadBundle\Entity\DoNotContact;
use Milex\LeadBundle\Model\DoNotContact as DoNotContactModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class FeedbackLoop implements ProcessorInterface
{
    /**
     * @var ContactFinder
     */
    private $contactFinder;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Message
     */
    private $message;

    /**
     * @var DoNotContactModel
     */
    private $doNotContact;

    /**
     * FeedbackLoop constructor.
     */
    public function __construct(
        ContactFinder $contactFinder,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        DoNotContactModel $doNotContact
    ) {
        $this->contactFinder = $contactFinder;
        $this->translator    = $translator;
        $this->logger        = $logger;
        $this->doNotContact  = $doNotContact;
    }

    /**
     * @return bool
     */
    public function process(Message $message)
    {
        $this->message = $message;
        $this->logger->debug('MONITORED EMAIL: Processing message ID '.$this->message->id.' for a feedback loop report');

        if (!$this->isApplicable()) {
            return false;
        }

        try {
            $parser = new Parser($this->message);
            if (!$contactEmail = $parser->parse()) {
                // A contact email was not found in the FBL report
                return false;
            }
        } catch (FeedbackLoopNotFound $exception) {
            return false;
        }

        $this->logger->debug('MONITORED EMAIL: Found '.$contactEmail.' in feedback loop report');

        $searchResult = $this->contactFinder->find($contactEmail);
        if (!$contacts = $searchResult->getContacts()) {
            return false;
        }

        $comments = $this->translator->trans('milex.email.bounce.reason.spam');
        foreach ($contacts as $contact) {
            $this->doNotContact->addDncForContact($contact->getId(), 'email', DoNotContact::UNSUBSCRIBED, $comments);
        }

        return true;
    }

    /**
     * @return int
     */
    protected function isApplicable()
    {
        return preg_match('/.*feedback-type: abuse.*/is', $this->message->fblReport);
    }
}
