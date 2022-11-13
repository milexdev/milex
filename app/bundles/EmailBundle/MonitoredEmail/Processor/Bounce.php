<?php

namespace Milex\EmailBundle\MonitoredEmail\Processor;

use Milex\CoreBundle\Helper\DateTimeHelper;
use Milex\EmailBundle\Entity\Email;
use Milex\EmailBundle\Entity\Stat;
use Milex\EmailBundle\Entity\StatRepository;
use Milex\EmailBundle\MonitoredEmail\Exception\BounceNotFound;
use Milex\EmailBundle\MonitoredEmail\Message;
use Milex\EmailBundle\MonitoredEmail\Processor\Bounce\BouncedEmail;
use Milex\EmailBundle\MonitoredEmail\Processor\Bounce\Parser;
use Milex\EmailBundle\MonitoredEmail\Search\ContactFinder;
use Milex\EmailBundle\Swiftmailer\Transport\BounceProcessorInterface;
use Milex\LeadBundle\Model\DoNotContact;
use Milex\LeadBundle\Model\LeadModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Bounce implements ProcessorInterface
{
    /**
     * @var \Swift_Transport
     */
    protected $transport;

    /**
     * @var ContactFinder
     */
    protected $contactFinder;

    /**
     * @var StatRepository
     */
    protected $statRepository;

    /**
     * @var LeadModel
     */
    protected $leadModel;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $bouncerAddress;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var DoNotContact
     */
    protected $doNotContact;

    /**
     * Bounce constructor.
     */
    public function __construct(
        \Swift_Transport $transport,
        ContactFinder $contactFinder,
        StatRepository $statRepository,
        LeadModel $leadModel,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        DoNotContact $doNotContact
    ) {
        $this->transport      = $transport;
        $this->contactFinder  = $contactFinder;
        $this->statRepository = $statRepository;
        $this->leadModel      = $leadModel;
        $this->translator     = $translator;
        $this->logger         = $logger;
        $this->doNotContact   = $doNotContact;
    }

    /**
     * @return bool
     */
    public function process(Message $message)
    {
        $this->message = $message;
        $bounce        = false;

        $this->logger->debug('MONITORED EMAIL: Processing message ID '.$this->message->id.' for a bounce');

        // Does the transport have special handling such as Amazon SNS?
        if ($this->transport instanceof BounceProcessorInterface) {
            try {
                $bounce = $this->transport->processBounce($this->message);
            } catch (BounceNotFound $exception) {
                // Attempt to parse a bounce the standard way
            }
        }

        if (!$bounce) {
            try {
                $bounce = (new Parser($this->message))->parse();
            } catch (BounceNotFound $exception) {
                return false;
            }
        }

        $searchResult = $this->contactFinder->find($bounce->getContactEmail(), $bounce->getBounceAddress());
        if (!$contacts = $searchResult->getContacts()) {
            // No contacts found so bail
            return false;
        }

        $stat    = $searchResult->getStat();
        $channel = 'email';
        if ($stat) {
            // Update stat entry
            $this->updateStat($stat, $bounce);

            if ($stat->getEmail() instanceof Email) {
                // We know the email ID so set it to append to the the DNC record
                $channel = ['email' => $stat->getEmail()->getId()];
            }
        }

        $comments = $this->translator->trans('milex.email.bounce.reason.'.$bounce->getRuleCategory());
        foreach ($contacts as $contact) {
            $this->doNotContact->addDncForContact($contact->getId(), $channel, \Milex\LeadBundle\Entity\DoNotContact::BOUNCED, $comments);
        }

        return true;
    }

    protected function updateStat(Stat $stat, BouncedEmail $bouncedEmail)
    {
        $dtHelper    = new DateTimeHelper();
        $openDetails = $stat->getOpenDetails();

        if (!isset($openDetails['bounces'])) {
            $openDetails['bounces'] = [];
        }

        $openDetails['bounces'][] = [
            'datetime' => $dtHelper->toUtcString(),
            'reason'   => $bouncedEmail->getRuleCategory(),
            'code'     => $bouncedEmail->getRuleNumber(),
            'type'     => $bouncedEmail->getType(),
        ];

        $stat->setOpenDetails($openDetails);

        $retryCount = $stat->getRetryCount();
        ++$retryCount;
        $stat->setRetryCount($retryCount);

        if ($fail = $bouncedEmail->isFinal() || $retryCount >= 5) {
            $stat->setIsFailed(true);
        }

        $this->statRepository->saveEntity($stat);
    }
}
