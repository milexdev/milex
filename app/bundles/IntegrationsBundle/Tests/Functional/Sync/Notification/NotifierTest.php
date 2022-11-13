<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Tests\Functional\Sync\Notification;

use Milex\CoreBundle\Test\MilexMysqlTestCase;
use Milex\IntegrationsBundle\Helper\SyncIntegrationsHelper;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\NotificationDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use Milex\IntegrationsBundle\Sync\Notification\Notifier;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\MilexSyncDataExchange;
use Milex\IntegrationsBundle\Tests\Functional\Services\SyncService\TestExamples\Integration\ExampleIntegration;
use Milex\IntegrationsBundle\Tests\Functional\Services\SyncService\TestExamples\Sync\SyncDataExchange\ExampleSyncDataExchange;
use Milex\LeadBundle\DataFixtures\ORM\LoadLeadData;
use Milex\LeadBundle\Entity\Lead;

class NotifierTest extends MilexMysqlTestCase
{
    public function testNotifications(): void
    {
        $this->installDatabaseFixtures([LoadLeadData::class]);

        $leadRepository = $this->em->getRepository(Lead::class);
        /** @var Lead[] $leads */
        $leads = $leadRepository->findBy([], [], 2);

        /** @var SyncIntegrationsHelper $syncIntegrationsHelper */
        $syncIntegrationsHelper = self::$container->get('milex.integrations.helper.sync_integrations');
        $syncIntegrationsHelper->addIntegration(new ExampleIntegration(new ExampleSyncDataExchange()));

        /** @var Notifier $notifier */
        $notifier = self::$container->get('milex.integrations.sync.notifier');

        $contactNotification = new NotificationDAO(
            new ObjectChangeDAO(
                ExampleIntegration::NAME,
                'Foo',
                1,
                Contact::NAME,
                (int) $leads[0]->getId()
            ),
            'This is the message'
        );
        $companyNotification = new NotificationDAO(
            new ObjectChangeDAO(
                ExampleIntegration::NAME,
                'Bar',
                2,
                MilexSyncDataExchange::OBJECT_COMPANY,
                (int) $leads[1]->getId()
            ),
            'This is the message'
        );

        $notifier->noteMilexSyncIssue([$contactNotification, $companyNotification]);
        $notifier->finalizeNotifications();

        // Check audit log
        $qb = $this->connection->createQueryBuilder();
        $qb->select('1')
            ->from(MILEX_TABLE_PREFIX.'audit_log')
            ->where(
                $qb->expr()->eq('bundle', $qb->expr()->literal(ExampleIntegration::NAME))
            );

        $this->assertCount(2, $qb->execute()->fetchAll());

        // Contact event log
        $qb = $this->connection->createQueryBuilder();
        $qb->select('1')
            ->from(MILEX_TABLE_PREFIX.'lead_event_log')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('bundle', $qb->expr()->literal('integrations')),
                    $qb->expr()->eq('object', $qb->expr()->literal(ExampleIntegration::NAME))
                )
            );
        $this->assertCount(1, $qb->execute()->fetchAll());

        // User notifications
        $qb = $this->connection->createQueryBuilder();
        $qb->select('1')
            ->from(MILEX_TABLE_PREFIX.'notifications')
            ->where(
                $qb->expr()->eq('icon_class', $qb->expr()->literal('fa-refresh'))
            );
        $this->assertCount(2, $qb->execute()->fetchAll());
    }
}
