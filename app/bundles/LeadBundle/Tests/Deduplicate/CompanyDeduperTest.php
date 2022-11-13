<?php

namespace Milex\LeadBundle\Tests\Deduplicate;

use Milex\LeadBundle\Deduplicate\CompanyDeduper;
use Milex\LeadBundle\Entity\CompanyRepository;
use Milex\LeadBundle\Exception\UniqueFieldNotFoundException;
use Milex\LeadBundle\Model\FieldModel;

class CompanyDeduperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FieldModel
     */
    private $fieldModel;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|CompanyRepository
     */
    private $companyRepository;

    protected function setUp(): void
    {
        $this->fieldModel = $this->getMockBuilder(FieldModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->companyRepository = $this->getMockBuilder(CompanyRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testUniqueFieldNotFoundException()
    {
        $this->expectException(UniqueFieldNotFoundException::class);
        $this->fieldModel->method('getFieldList')->willReturn([]);
        $this->getDeduper()->checkForDuplicateCompanies([]);
    }

    /**
     * @return CompanyDeduper
     */
    private function getDeduper()
    {
        return new CompanyDeduper(
            $this->fieldModel,
            $this->companyRepository
        );
    }
}
