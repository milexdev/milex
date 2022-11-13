<?php

namespace Milex\ReportBundle\Scheduler\Model;

use Milex\CoreBundle\Form\DataTransformer\ArrayStringTransformer;
use Milex\EmailBundle\Helper\MailHelper;
use Milex\ReportBundle\Entity\Scheduler;
use Milex\ReportBundle\Exception\FileTooBigException;

class SendSchedule
{
    /**
     * @var MailHelper
     */
    private $mailer;

    /**
     * @var MessageSchedule
     */
    private $messageSchedule;

    /**
     * @var FileHandler
     */
    private $fileHandler;

    public function __construct(
        MailHelper $mailer,
        MessageSchedule $messageSchedule,
        FileHandler $fileHandler
    ) {
        $this->mailer          = $mailer->getMailer();
        $this->messageSchedule = $messageSchedule;
        $this->fileHandler     = $fileHandler;
    }

    /**
     * @param string $filePath
     */
    public function send(Scheduler $scheduler, $csvFilePath)
    {
        $this->mailer->reset(true);

        $transformer = new ArrayStringTransformer();
        $report      = $scheduler->getReport();
        $emails      = $transformer->reverseTransform($report->getToAddress());
        $subject     = $this->messageSchedule->getSubject($report);
        $message     = $this->messageSchedule->getMessageForAttachedFile($report);

        try {
            // Try to send the CSV file as an email attachement.
            $this->fileHandler->fileCanBeAttached($csvFilePath);
            $this->mailer->attachFile($csvFilePath, basename($csvFilePath), 'text/csv');
        } catch (FileTooBigException $e) {
            $zipFilePath = $this->fileHandler->zipIt($csvFilePath);
            try {
                // Try to send the ZIP file as an email attachement.
                $this->fileHandler->fileCanBeAttached($zipFilePath);
                $this->mailer->attachFile($zipFilePath, basename($zipFilePath), 'application/zip');
            } catch (FileTooBigException $e) {
                // Send the ZIP file as link in the email message.
                $this->fileHandler->moveZipToPermanentLocation($report, $zipFilePath);
                $message = $this->messageSchedule->getMessageForLinkedFile($report);
            }
        }

        $this->mailer->setTo($emails);
        $this->mailer->setSubject($subject);
        $this->mailer->setBody($message);
        $this->mailer->parsePlainText($message);
        $this->mailer->send(true);
    }
}
