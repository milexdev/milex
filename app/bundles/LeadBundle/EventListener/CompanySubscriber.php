<?php

namespace Milex\LeadBundle\EventListener;

use Milex\CoreBundle\Helper\IpLookupHelper;
use Milex\CoreBundle\Model\AuditLogModel;
use Milex\LeadBundle\Event as Events;
use Milex\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CompanySubscriber implements EventSubscriberInterface
{
    /**
     * @var AuditLogModel
     */
    private $auditLogModel;

    /**
     * @var IpLookupHelper
     */
    private $ipLookupHelper;

    public function __construct(IpLookupHelper $ipLookupHelper, AuditLogModel $auditLogModel)
    {
        $this->ipLookupHelper = $ipLookupHelper;
        $this->auditLogModel  = $auditLogModel;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::COMPANY_POST_SAVE   => ['onCompanyPostSave', 0],
            LeadEvents::COMPANY_POST_DELETE => ['onCompanyDelete', 0],
        ];
    }

    /**
     * Add a company entry to the audit log.
     */
    public function onCompanyPostSave(Events\CompanyEvent $event)
    {
        $company = $event->getCompany();
        if ($details = $event->getChanges()) {
            $log = [
                'bundle'    => 'lead',
                'object'    => 'company',
                'objectId'  => $company->getId(),
                'action'    => ($event->isNew()) ? 'create' : 'update',
                'details'   => $details,
                'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
            ];
            $this->auditLogModel->writeToLog($log);
        }
    }

    /**
     * Add a company delete entry to the audit log.
     */
    public function onCompanyDelete(Events\CompanyEvent $event)
    {
        $company = $event->getCompany();
        $log     = [
            'bundle'    => 'lead',
            'object'    => 'company',
            'objectId'  => $company->deletedId,
            'action'    => 'delete',
            'details'   => ['name', $company->getPrimaryIdentifier()],
            'ipAddress' => $this->ipLookupHelper->getIpAddressFromRequest(),
        ];
        $this->auditLogModel->writeToLog($log);
    }
}
