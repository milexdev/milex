<?php

declare(strict_types=1);

namespace Milex\WebhookBundle\Tests\Notificator;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\Helper\CoreParametersHelper;
use Milex\CoreBundle\Model\NotificationModel;
use Milex\EmailBundle\Helper\MailHelper;
use Milex\UserBundle\Entity\User;
use Milex\WebhookBundle\Entity\Webhook;
use Milex\WebhookBundle\Notificator\WebhookKillNotificator;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

class WebhookKillNotificatorTest extends \PHPUnit\Framework\TestCase
{
    public function testSendToOwner(): void
    {
        $subject        = 'subject';
        $reason         = 'reason';
        $webhookId      = 1;
        $webhookName    = 'Webhook name';
        $generatedRoute = 'generatedRoute';
        $details        = 'details';
        $createdBy      = 'createdBy';
        $ownerEmail     = 'toEmail';
        $modifiedBy     = null;
        $htmlUrl        = '<a href="'.$generatedRoute.'" data-toggle="ajax">'.$webhookName.'</a>';

        $owner                 = $this->createMock(User::class);
        $translatorMock        = $this->createMock(TranslatorInterface::class);
        $webhook               = $this->createMock(Webhook::class);
        $routerMock            = $this->createMock(Router::class);
        $entityManagerMock     = $this->createMock(EntityManager::class);
        $notificationModelMock = $this->createMock(NotificationModel::class);
        $mailHelperMock        = $this->createMock(MailHelper::class);

        $translatorMock->method('trans')
            ->withConsecutive(
                ['milex.webhook.stopped'],
                [$reason],
                [
                    'milex.webhook.stopped.details',
                    ['%reason%'  => $reason, '%webhook%' => $htmlUrl],
                ]
            )
            ->willReturnOnConsecutiveCalls($subject, $reason, $details);
        $coreParamHelperMock = $this->createMock(CoreParametersHelper::class);
        $coreParamHelperMock
            ->method('get')
            ->with('webhook_send_notification_to_author')
            ->willReturn(1);

        $webhook = $this->createMock(Webhook::class);
        $webhook->expects($this->once())
            ->method('getId')
            ->willReturn($webhookId);

        $webhook->expects($this->once())
            ->method('getName')
            ->willReturn($webhookName);

        $routerMock->expects($this->once())
            ->method('generate')
            ->with(
                'milex_webhook_action',
                ['objectAction' => 'view', 'objectId' => $webhookId],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn($generatedRoute);

        $webhook->expects($this->once())
            ->method('getCreatedBy')
            ->willReturn($createdBy);

        $webhook->expects($this->once())
            ->method('getModifiedBy')
            ->willReturn($modifiedBy);

        $entityManagerMock->expects($this->once())
            ->method('getReference')
            ->with('MilexUserBundle:User', $createdBy)
            ->willReturn($owner);

        $notificationModelMock->expects($this->once())
            ->method('addNotification')
            ->with(
                $details,
                'error',
                false,
                $subject,
                null,
                false,
                $owner
            );

        $owner->expects($this->once())
            ->method('getEmail')
            ->willReturn($ownerEmail);

        $mailHelperMock
            ->expects($this->once())
            ->method('setTo')
            ->with($ownerEmail);

        $mailHelperMock
            ->expects($this->once())
            ->method('setSubject')
            ->with($subject);

        $mailHelperMock
            ->expects($this->once())
            ->method('setBody')
            ->with($details);

        $webhookKillNotificator = new WebhookKillNotificator($translatorMock, $routerMock, $notificationModelMock, $entityManagerMock, $mailHelperMock, $coreParamHelperMock);
        $webhookKillNotificator->send($webhook, $reason);
    }

    public function testSendToModifier(): void
    {
        $subject        = 'subject';
        $reason         = 'reason';
        $webhookId      = 1;
        $webhookName    = 'Webhook name';
        $generatedRoute = 'generatedRoute';
        $details        = 'details';
        $createdBy      = 'createdBy';
        $ownerEmail     = 'ownerEmail';
        $modifiedBy     = 'modifiedBy';
        $modifierEmail  = 'modifierEmail';
        $htmlUrl        = '<a href="'.$generatedRoute.'" data-toggle="ajax">'.$webhookName.'</a>';

        $owner                 = $this->createMock(User::class);
        $modifier              = $this->createMock(User::class);
        $translatorMock        = $this->createMock(TranslatorInterface::class);
        $routerMock            = $this->createMock(Router::class);
        $entityManagerMock     = $this->createMock(EntityManager::class);
        $notificationModelMock = $this->createMock(NotificationModel::class);
        $mailHelperMock        = $this->createMock(MailHelper::class);

        $translatorMock->method('trans')
            ->withConsecutive(
                ['milex.webhook.stopped'],
                [$reason],
                [
                    'milex.webhook.stopped.details',
                    ['%reason%'  => $reason, '%webhook%' => $htmlUrl],
                ]
            )
            ->willReturnOnConsecutiveCalls($subject, $reason, $details);

        $coreParamHelperMock = $this->createMock(CoreParametersHelper::class);
        $coreParamHelperMock
            ->method('get')
            ->with('webhook_send_notification_to_author')
            ->willReturn(1);

        $webhook = $this->createMock(Webhook::class);
        $webhook->expects($this->once())
            ->method('getId')
            ->willReturn($webhookId);

        $webhook->expects($this->once())
            ->method('getName')
            ->willReturn($webhookName);

        $routerMock->expects($this->once())
            ->method('generate')
            ->with(
                'milex_webhook_action',
                ['objectAction' => 'view', 'objectId' => $webhookId],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn($generatedRoute);

        $webhook->expects($this->exactly(2))
            ->method('getCreatedBy')
            ->willReturn($createdBy);

        $webhook->expects($this->exactly(3))
            ->method('getModifiedBy')
            ->willReturn($modifiedBy);

        $entityManagerMock->expects($this->exactly(2))
            ->method('getReference')
            ->withConsecutive(
                ['MilexUserBundle:User', $createdBy],
                ['MilexUserBundle:User', $modifiedBy]
            )
            ->willReturnOnConsecutiveCalls($owner, $modifier);

        $notificationModelMock->expects($this->once())
            ->method('addNotification')
            ->with(
                $details,
                'error',
                false,
                $subject,
                null,
                false,
                $modifier
            );

        $owner->expects($this->once())
            ->method('getEmail')
            ->willReturn($ownerEmail);

        $modifier->expects($this->once())
            ->method('getEmail')
            ->willReturn($modifierEmail);

        $mailHelperMock->expects($this->once())
            ->method('setTo')
            ->with($modifierEmail);

        $mailHelperMock->expects($this->once())
            ->method('setCc')
            ->with($ownerEmail);

        $mailHelperMock->expects($this->once())
            ->method('setSubject')
            ->with($subject);

        $mailHelperMock->expects($this->once())
            ->method('setBody')
            ->with($details);

        $webhookKillNotificator = new WebhookKillNotificator($translatorMock, $routerMock, $notificationModelMock, $entityManagerMock, $mailHelperMock, $coreParamHelperMock);
        $webhookKillNotificator->send($webhook, $reason);
    }
}
