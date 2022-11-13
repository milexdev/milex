<?php

namespace Milex\EmailBundle\Model;

use Doctrine\ORM\ORMException;
use Milex\CoreBundle\Event\TokenReplacementEvent;
use Milex\CoreBundle\Exception\InvalidValueException;
use Milex\CoreBundle\Exception\RecordException;
use Milex\CoreBundle\Helper\ArrayHelper;
use Milex\EmailBundle\EmailEvents;
use Milex\EmailBundle\Exception\EmailCouldNotBeSentException;
use Milex\EmailBundle\Exception\InvalidEmailException;
use Milex\EmailBundle\Helper\EmailValidator;
use Milex\EmailBundle\OptionsAccessor\EmailToUserAccessor;
use Milex\LeadBundle\DataObject\ContactFieldToken;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Exception\InvalidContactFieldTokenException;
use Milex\LeadBundle\Validator\CustomFieldValidator;
use Milex\UserBundle\Hash\UserHash;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SendEmailToUser
{
    private EmailModel $emailModel;

    private EventDispatcherInterface $dispatcher;

    private CustomFieldValidator $customFieldValidator;

    private EmailValidator $emailValidator;

    public function __construct(
        EmailModel $emailModel,
        EventDispatcherInterface $dispatcher,
        CustomFieldValidator $customFieldValidator,
        EmailValidator $emailValidator
    ) {
        $this->emailModel           = $emailModel;
        $this->dispatcher           = $dispatcher;
        $this->customFieldValidator = $customFieldValidator;
        $this->emailValidator       = $emailValidator;
    }

    /**
     * @throws EmailCouldNotBeSentException
     * @throws ORMException
     */
    public function sendEmailToUsers(array $config, Lead $lead)
    {
        $emailToUserAccessor = new EmailToUserAccessor($config);

        $email = $this->emailModel->getEntity($emailToUserAccessor->getEmailID());

        if (!$email || !$email->isPublished()) {
            throw new EmailCouldNotBeSentException('Email not found or published');
        }

        $leadCredentials = $lead->getProfileFields();

        $to  = ArrayHelper::removeEmptyValues($this->replaceTokens($emailToUserAccessor->getToFormatted(), $lead));
        $cc  = ArrayHelper::removeEmptyValues($this->replaceTokens($emailToUserAccessor->getCcFormatted(), $lead));
        $bcc = ArrayHelper::removeEmptyValues($this->replaceTokens($emailToUserAccessor->getBccFormatted(), $lead));

        $users  = $emailToUserAccessor->getUserIdsToSend($lead->getOwner());
        $idHash = UserHash::getFakeUserHash();
        $tokens = $this->emailModel->dispatchEmailSendEvent($email, $leadCredentials, $idHash)->getTokens();
        $errors = $this->emailModel->sendEmailToUser($email, $users, $leadCredentials, $tokens, [], false, $to, $cc, $bcc);

        if ($errors) {
            throw new EmailCouldNotBeSentException(implode(', ', $errors));
        }
    }

    /**
     * @param string[] $emailAddressesOrTokens
     *
     * @return string[]
     */
    private function replaceTokens(array $emailAddressesOrTokens, Lead $lead): array
    {
        return array_map($this->makeTokenReplacerCallback($lead), $emailAddressesOrTokens);
    }

    private function makeTokenReplacerCallback(Lead $lead): callable
    {
        return function (string $emailAddressOrToken) use ($lead): string {
            try {
                $contactFieldToken = new ContactFieldToken($emailAddressOrToken);
            } catch (InvalidContactFieldTokenException $e) {
                try {
                    $this->emailValidator->validate($emailAddressOrToken);

                    return $emailAddressOrToken;
                } catch (InvalidEmailException $e) {
                    return '';
                }
            }

            // The values are validated on form save.
            // But ensure the custom field is still valid on email send before asking for the replacement value.
            try {
                // Validate that the contact field exists and is type of email.
                $this->customFieldValidator->validateFieldType($contactFieldToken->getFieldAlias(), 'email');

                return $this->replaceToken($contactFieldToken->getFullToken(), $lead);
            } catch (InvalidValueException | RecordException $e) {
                // If the field does not exist or is not type of email then use the default value.
                return (string) $contactFieldToken->getDefaultValue();
            }
        };
    }

    private function replaceToken(string $token, Lead $lead): string
    {
        $tokenEvent = new TokenReplacementEvent($token, $lead);
        $this->dispatcher->dispatch(EmailEvents::ON_EMAIL_ADDRESS_TOKEN_REPLACEMENT, $tokenEvent);

        return $tokenEvent->getContent();
    }
}
