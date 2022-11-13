<?php

namespace Milex\CoreBundle\Model;

use Milex\CoreBundle\Entity\AuditLog;
use Milex\UserBundle\Entity\User;

/**
 * Class AuditLogModel.
 */
class AuditLogModel extends AbstractCommonModel
{
    /**
     * {@inheritdoc}
     *
     * @return \Milex\CoreBundle\Entity\AuditLogRepository
     */
    public function getRepository()
    {
        return $this->em->getRepository('MilexCoreBundle:AuditLog');
    }

    /**
     * Writes an entry to the audit log.
     *
     * @param array $args [bundle, object, objectId, action, details, ipAddress]
     */
    public function writeToLog(array $args)
    {
        $bundle    = (isset($args['bundle'])) ? $args['bundle'] : '';
        $object    = (isset($args['object'])) ? $args['object'] : '';
        $objectId  = (isset($args['objectId'])) ? $args['objectId'] : '';
        $action    = (isset($args['action'])) ? $args['action'] : '';
        $details   = (isset($args['details'])) ? $args['details'] : '';
        $ipAddress = (isset($args['ipAddress'])) ? $args['ipAddress'] : '';

        $log = new AuditLog();
        $log->setBundle($bundle);
        $log->setObject($object);
        $log->setObjectId($objectId);
        $log->setAction($action);
        $log->setDetails($details);
        $log->setIpAddress($ipAddress);
        $log->setDateAdded(new \DateTime());

        $user     = (!defined('MILEX_IGNORE_AUDITLOG_USER') && !defined('MILEX_AUDITLOG_USER')) ? $this->userHelper->getUser() : null;
        $userId   = 0;
        $userName = defined('MILEX_AUDITLOG_USER') ? MILEX_AUDITLOG_USER : $this->translator->trans('milex.core.system');
        if ($user instanceof User && $user->getId()) {
            $userId   = $user->getId();
            $userName = $user->getName();
        }
        $log->setUserId($userId);
        $log->setUserName($userName);

        $this->em->getRepository('MilexCoreBundle:AuditLog')->saveEntity($log);

        $this->em->detach($log);
    }

    /**
     * Get the audit log for specific object.
     *
     * @param string                  $object
     * @param string|int              $id
     * @param \DateTimeInterface|null $afterDate
     * @param int                     $limit
     * @param string|null             $bundle
     *
     * @return mixed
     */
    public function getLogForObject($object, $id, $afterDate = null, $limit = 10, $bundle = null)
    {
        return $this->getRepository()->getLogForObject($object, $id, $limit, $afterDate, $bundle);
    }
}
