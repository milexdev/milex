<?php

namespace Milex\LeadBundle\Tests\Model;

use Milex\CoreBundle\Translation\Translator;
use Milex\FormBundle\Entity\Field;
use Milex\LeadBundle\Model\CompanyReportData;
use Milex\LeadBundle\Model\FieldModel;
use Milex\ReportBundle\Event\ReportGeneratorEvent;
use Symfony\Component\Translation\TranslatorInterface;

class CompanyReportDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    protected function setUp(): void
    {
        $this->translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->translator->method('trans')
            ->willReturnCallback(
                function ($key) {
                    return $key;
                }
            );
    }

    /**
     * @covers \Milex\LeadBundle\Model\CompanyReportData::getCompanyData
     */
    public function testGetCompanyData()
    {
        $fieldModelMock = $this->getMockBuilder(FieldModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $field1 = new Field();
        $field1->setType('boolean');
        $field1->setAlias('boolField');
        $field1->setLabel('boolFieldLabel');

        $field2 = new Field();
        $field2->setType('email');
        $field2->setAlias('emailField');
        $field2->setLabel('emailFieldLabel');

        $fields = [
            $field1,
            $field2,
        ];

        $fieldModelMock->expects($this->once())
            ->method('getEntities')
            ->willReturn($fields);

        $companyReportData = new CompanyReportData($fieldModelMock, $this->translator);

        $result = $companyReportData->getCompanyData();

        $expected = [
            'comp.id' => [
                'alias' => 'comp_id',
                'label' => 'milex.lead.report.company.company_id',
                'type'  => 'int',
                'link'  => 'milex_company_action',
            ],
            'companies_lead.is_primary' => [
                'label' => 'milex.lead.report.company.is_primary',
                'type'  => 'bool',
            ],
            'companies_lead.date_added' => [
                'label' => 'milex.lead.report.company.date_added',
                'type'  => 'datetime',
            ],
            'comp.boolField' => [
                'label' => 'milex.report.field.company.label',
                'type'  => 'bool',
            ],
            'comp.emailField' => [
                'label' => 'milex.report.field.company.label',
                'type'  => 'email',
            ],
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * @covers \Milex\LeadBundle\Model\CompanyReportData::eventHasCompanyColumns
     */
    public function testEventHasCompanyColumns()
    {
        $fieldModelMock = $this->getMockBuilder(FieldModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eventMock = $this->getMockBuilder(ReportGeneratorEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $field = new Field();
        $field->setType('email');
        $field->setAlias('email');
        $field->setLabel('Email');

        $fieldModelMock->expects($this->once())
            ->method('getEntities')
            ->willReturn([$field]);

        $eventMock->expects($this->once())
            ->method('hasColumn')
            ->with('comp.id')
            ->willReturn(true);

        $companyReportData = new CompanyReportData($fieldModelMock, $this->translator);

        $result = $companyReportData->eventHasCompanyColumns($eventMock);

        $this->assertTrue($result);
    }

    /**
     * @covers \Milex\LeadBundle\Model\CompanyReportData::eventHasCompanyColumns
     */
    public function testEventDoesNotHaveCompanyColumns()
    {
        $fieldModelMock = $this->getMockBuilder(FieldModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $eventMock = $this->getMockBuilder(ReportGeneratorEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $field = new Field();
        $field->setType('email');
        $field->setAlias('email');
        $field->setLabel('Email');

        $fieldModelMock->expects($this->once())
            ->method('getEntities')
            ->willReturn([$field]);

        $eventMock->expects($this->any())
            ->method('hasColumn')
            ->willReturn(false);

        $companyReportData = new CompanyReportData($fieldModelMock, $this->translator);

        $result = $companyReportData->eventHasCompanyColumns($eventMock);

        $this->assertFalse($result);
    }
}
