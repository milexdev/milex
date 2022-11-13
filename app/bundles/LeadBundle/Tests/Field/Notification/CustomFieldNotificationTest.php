<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Tests\Field\Notification;

use Milex\CoreBundle\Model\NotificationModel;
use Milex\LeadBundle\Entity\LeadField;
use Milex\LeadBundle\Field\Notification\CustomFieldNotification;
use Milex\UserBundle\Entity\User;
use Milex\UserBundle\Model\UserModel;
use Symfony\Component\Translation\TranslatorInterface;

class CustomFieldNotificationTest extends \PHPUnit\Framework\TestCase
{
    public function testNoUserId(): void
    {
        $notificationModel   = $this->createMock(NotificationModel::class);
        $userModel           = $this->createMock(UserModel::class);
        $translatorInterface = $this->createMock(TranslatorInterface::class);

        $leadField = new LeadField();

        $userModel->expects($this->never())
            ->method('getEntity');

        $customFieldNotification = new CustomFieldNotification($notificationModel, $userModel, $translatorInterface);

        $customFieldNotification->customFieldWasCreated($leadField, 0);
    }

    public function testNoUser(): void
    {
        $notificationModel   = $this->createMock(NotificationModel::class);
        $userModel           = $this->createMock(UserModel::class);
        $translatorInterface = $this->createMock(TranslatorInterface::class);

        $leadField = new LeadField();

        $userModel->expects($this->once())
            ->method('getEntity')
            ->willReturn(null);

        $translatorInterface->expects($this->never())
            ->method('trans');

        $customFieldNotification = new CustomFieldNotification($notificationModel, $userModel, $translatorInterface);

        $customFieldNotification->customFieldWasCreated($leadField, 1);
    }

    public function testCustomFieldWasCreated(): void
    {
        $notificationModel   = $this->createMock(NotificationModel::class);
        $userModel           = $this->createMock(UserModel::class);
        $translatorInterface = $this->createMock(TranslatorInterface::class);

        $userId    = 1;
        $leadField = new LeadField();
        $user      = new User();

        $userModel->expects($this->once())
            ->method('getEntity')
            ->with($userId)
            ->willReturn($user);

        $translatorInterface->expects($this->exactly(2))
            ->method('trans')
            ->willReturn('text');

        $notificationModel->expects($this->once())
            ->method('addNotification')
            ->with(
                'text',
                'info',
                false,
                'text',
                'fa-columns',
                null,
                $user
            );

        $customFieldNotification = new CustomFieldNotification($notificationModel, $userModel, $translatorInterface);

        $customFieldNotification->customFieldWasCreated($leadField, $userId);
    }
}
