<?php

namespace Milex\LeadBundle\Tests\EventListener;

use Milex\CampaignBundle\Event\CampaignExecutionEvent;
use Milex\CampaignBundle\Model\CampaignModel;
use Milex\CoreBundle\Helper\CoreParametersHelper;
use Milex\CoreBundle\Helper\IpLookupHelper;
use Milex\LeadBundle\Entity\Company;
use Milex\LeadBundle\Entity\CompanyLeadRepository;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\EventListener\CampaignSubscriber;
use Milex\LeadBundle\Model\CompanyModel;
use Milex\LeadBundle\Model\FieldModel;
use Milex\LeadBundle\Model\LeadModel;
use Milex\LeadBundle\Model\ListModel;

class CampaignSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /** @var array */
    private $configFrom = [
        'id'          => 111,
        'companyname' => 'Milex',
        'companemail' => 'milex@milex.com',
    ];

    private $configTo = [
        'id'          => '112',
        'companyname' => 'Milex2',
        'companemail' => 'milex@milexsecond.com',
    ];

    public function testOnCampaignTriggerActiononUpdateCompany()
    {
        $mockIpLookupHelper = $this->createMock(IpLookupHelper::class);
        $mockLeadModel      = $this->createMock(LeadModel::class);
        $mockLeadFieldModel = $this->createMock(FieldModel::class);
        $mockListModel      = $this->createMock(ListModel::class);
        $mockCompanyModel   = $this->createMock(CompanyModel::class);
        $mockCampaignModel  = $this->createMock(CampaignModel::class);
        $companyEntityFrom  = $this->createMock(Company::class);

        $companyEntityFrom->method('getId')
            ->willReturn($this->configFrom['id']);
        $companyEntityFrom->method('getName')
            ->willReturn($this->configFrom['companyname']);

        $companyEntityTo = $this->createMock(Company::class);
        $companyEntityTo->method('getId')
            ->willReturn($this->configTo['id']);
        $companyEntityTo->method('getName')
            ->willReturn($this->configTo['companyname']);
        $companyEntityTo->method('getProfileFields')
            ->willReturn($this->configTo);

        $mockCompanyModel->expects($this->once())->method('getEntity')->willReturn($companyEntityFrom);

        $mockCompanyLeadRepo  = $this->createMock(CompanyLeadRepository::class);
        $mockCompanyLeadRepo->expects($this->once())->method('getCompaniesByLeadId')->willReturn(null);

        $mockCompanyModel->expects($this->once())
            ->method('getCompanyLeadRepository')
            ->willReturn($mockCompanyLeadRepo);

        $mockCompanyModel->expects($this->once())
            ->method('checkForDuplicateCompanies')
            ->willReturn([$companyEntityTo]);

        $mockCompanyModel->expects($this->any())
            ->method('fetchCompanyFields')
            ->willReturn([['alias' => 'companyname']]);

        $mockCoreParametersHelper = $this->createMock(CoreParametersHelper::class);
        $mockCoreParametersHelper->method('get')
            ->with('default_timezone')
            ->willReturn('UTC');

        $subscriber = new CampaignSubscriber(
            $mockIpLookupHelper,
            $mockLeadModel,
            $mockLeadFieldModel,
            $mockListModel,
            $mockCompanyModel,
            $mockCampaignModel,
            $mockCoreParametersHelper
        );

        /** @var LeadModel $leadModel */
        $lead = new Lead();
        $lead->setId(99);
        $lead->setPrimaryCompany($this->configFrom);

        $mockLeadModel->expects($this->once())->method('setPrimaryCompany')->willReturnCallback(
            function () use ($lead) {
                $lead->setPrimaryCompany($this->configTo);
            }
        );

        $args = [
            'lead'  => $lead,
            'event' => [
                'type'       => 'lead.updatecompany',
                'properties' => $this->configTo,
            ],
            'eventDetails'    => [],
            'systemTriggered' => true,
            'eventSettings'   => [],
        ];

        $event = new CampaignExecutionEvent($args, true);
        $subscriber->onCampaignTriggerActionUpdateCompany($event);
        $this->assertTrue($event->getResult());

        $primaryCompany = $lead->getPrimaryCompany();
        $this->assertSame($this->configTo['companyname'], $primaryCompany['companyname']);
    }
}
