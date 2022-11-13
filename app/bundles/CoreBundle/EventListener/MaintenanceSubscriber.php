<?php

namespace Milex\CoreBundle\EventListener;

use Doctrine\DBAL\Connection;
use Milex\CoreBundle\CoreEvents;
use Milex\CoreBundle\Event\MaintenanceEvent;
use Milex\UserBundle\Entity\UserTokenRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MaintenanceSubscriber implements EventSubscriberInterface
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var UserTokenRepositoryInterface
     */
    private $userTokenRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        Connection $db,
        UserTokenRepositoryInterface $userTokenRepository,
        TranslatorInterface $translator
    ) {
        $this->db                  = $db;
        $this->userTokenRepository = $userTokenRepository;
        $this->translator          = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::MAINTENANCE_CLEANUP_DATA => ['onDataCleanup', -50],
        ];
    }

    public function onDataCleanup(MaintenanceEvent $event)
    {
        $this->cleanupData($event, 'audit_log');
        $this->cleanupData($event, 'notifications');

        $rows = $this->userTokenRepository->deleteExpired($event->isDryRun());
        $event->setStat($this->translator->trans('milex.maintenance.user_tokens'), $rows);
    }

    /**
     * @param string $table
     */
    private function cleanupData(MaintenanceEvent $event, $table)
    {
        $qb = $this->db->createQueryBuilder()
            ->setParameter('date', $event->getDate()->format('Y-m-d H:i:s'));

        if ($event->isDryRun()) {
            $rows = (int) $qb->select('count(*) as records')
                ->from(MILEX_TABLE_PREFIX.$table, 'log')
                ->where(
                    $qb->expr()->lte('log.date_added', ':date')
                )
                ->execute()
                ->fetchColumn();
        } else {
            $qb->select('log.id')
              ->from(MILEX_TABLE_PREFIX.$table, 'log')
              ->where(
                $qb->expr()->lte('log.date_added', ':date')
              );

            $rows = 0;
            $qb->setMaxResults(10000)->setFirstResult(0);

            $qb2 = $this->db->createQueryBuilder();
            while (true) {
                $ids = array_column($qb->execute()->fetchAll(), 'id');

                if (0 === sizeof($ids)) {
                    break;
                }

                $rows += $qb2->delete(MILEX_TABLE_PREFIX.$table)
                  ->where(
                    $qb2->expr()->in(
                      'id', $ids
                    )
                  )
                  ->execute();
            }
        }

        $event->setStat($this->translator->trans('milex.maintenance.'.$table), $rows, $qb->getSQL(), $qb->getParameters());
    }
}
