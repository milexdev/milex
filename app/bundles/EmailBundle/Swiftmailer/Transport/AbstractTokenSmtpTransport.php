<?php

namespace Milex\EmailBundle\Swiftmailer\Transport;

use Milex\EmailBundle\Swiftmailer\Message\MilexMessage;

/**
 * Class AbstractBatchTransport.
 */
abstract class AbstractTokenSmtpTransport extends \Swift_SmtpTransport implements TokenTransportInterface
{
    /**
     * @var \Swift_Mime_SimpleMessage
     */
    protected $message;

    /**
     * Do whatever is necessary to $this->message in order to deliver a batched payload. i.e. add custom headers, etc.
     */
    abstract protected function prepareMessage();

    /**
     * @param null $failedRecipients
     *
     * @return int
     *
     * @throws \Exception
     */
    public function send(\Swift_Mime_SimpleMessage $message, &$failedRecipients = null)
    {
        $this->message = $message;

        $this->prepareMessage();

        return parent::send($message, $failedRecipients);
    }

    /**
     * Get the metadata from a MilexMessage.
     *
     * @return array
     */
    public function getMetadata()
    {
        return ($this->message instanceof MilexMessage) ? $this->message->getMetadata() : [];
    }

    /**
     * Get attachments from a MilexMessage.
     *
     * @return array
     */
    public function getAttachments()
    {
        return ($this->message instanceof MilexMessage) ? $this->message->getAttachments() : [];
    }
}
