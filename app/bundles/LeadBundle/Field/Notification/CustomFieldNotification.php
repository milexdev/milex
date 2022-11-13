<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Field\Notification;

use Milex\CoreBundle\Model\NotificationModel;
use Milex\LeadBundle\Entity\LeadField;
use Milex\LeadBundle\Field\Exception\NoUserException;
use Milex\UserBundle\Entity\User;
use Milex\UserBundle\Model\UserModel;
use Symfony\Component\Translation\TranslatorInterface;

class CustomFieldNotification
{
    /**
     * @var NotificationModel
     */
    private $notificationModel;

    /**
     * @var UserModel
     */
    private $userModel;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        NotificationModel $notificationModel,
        UserModel $userModel,
        TranslatorInterface $translator
    ) {
        $this->notificationModel = $notificationModel;
        $this->userModel         = $userModel;
        $this->translator        = $translator;
    }

    public function customFieldWasCreated(LeadField $leadField, int $userId): void
    {
        try {
            $user = $this->getUser($userId);
        } catch (NoUserException $e) {
            return;
        }

        $message = $this->translator->trans(
            'milex.lead.field.notification.created_message',
            ['%label%' => $leadField->getLabel()]
        );
        $header  = $this->translator->trans('milex.lead.field.notification.created_header');

        $this->addToNotificationCenter($user, $message, $header);
    }

    public function customFieldLimitWasHit(LeadField $leadField, int $userId): void
    {
        try {
            $user = $this->getUser($userId);
        } catch (NoUserException $e) {
            return;
        }

        $message = $this->translator->trans(
            'milex.lead.field.notification.custom_field_limit_hit_message',
            ['%label%' => $leadField->getLabel()]
        );
        $header  = $this->translator->trans('milex.lead.field.notification.custom_field_limit_hit_header');

        $this->addToNotificationCenter($user, $message, $header);
    }

    public function customFieldCannotBeCreated(LeadField $leadField, int $userId): void
    {
        try {
            $user = $this->getUser($userId);
        } catch (NoUserException $e) {
            return;
        }

        $message = $this->translator->trans(
            'milex.lead.field.notification.cannot_be_created_message',
            ['%label%' => $leadField->getLabel()]
        );
        $header  = $this->translator->trans('milex.lead.field.notification.cannot_be_created_header');

        $this->addToNotificationCenter($user, $message, $header);
    }

    private function addToNotificationCenter(User $user, string $message, string $header): void
    {
        $this->notificationModel->addNotification(
            $message,
            'info',
            false,
            $header,
            'fa-columns',
            null,
            $user
        );
    }

    /**
     * @throws NoUserException
     */
    private function getUser(int $userId): User
    {
        if (!$userId || !$user = $this->userModel->getEntity($userId)) {
            throw new NoUserException();
        }

        return $user;
    }
}
