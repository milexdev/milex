<?php

namespace Milex\LeadBundle\Tests\Report;

use Milex\FormBundle\Entity\Field;
use Milex\LeadBundle\Model\FieldModel;
use Milex\LeadBundle\Model\ListModel;
use Milex\LeadBundle\Report\FieldsBuilder;
use Milex\UserBundle\Model\UserModel;

class FieldsBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetLeadColumns()
    {
        $fieldModel = $this->getMockBuilder(FieldModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $listModel = $this->getMockBuilder(ListModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fieldModel->expects($this->exactly(2)) //We have 2 asserts
            ->method('getLeadFields')
            ->with()
            ->willReturn($this->getFields());

        $fieldsBuilder = new FieldsBuilder($fieldModel, $listModel, $userModel);

        $expected = [
            'l.id' => [
                'label' => 'milex.lead.report.contact_id',
                'type'  => 'int',
                'link'  => 'milex_contact_action',
            ],
            'i.ip_address' => [
                'label' => 'milex.core.ipaddress',
                'type'  => 'text',
            ],
            'l.date_identified' => [
                'label'          => 'milex.lead.report.date_identified',
                'type'           => 'datetime',
                'groupByFormula' => 'DATE(l.date_identified)',
            ],
            'l.points' => [
                'label' => 'milex.lead.points',
                'type'  => 'int',
            ],
            'l.owner_id' => [
                'label' => 'milex.lead.report.owner_id',
                'type'  => 'int',
                'link'  => 'milex_user_action',
            ],
            'u.first_name' => [
                'label' => 'milex.lead.report.owner_firstname',
                'type'  => 'string',
            ],
            'u.last_name' => [
                'label' => 'milex.lead.report.owner_lastname',
                'type'  => 'string',
            ],
            'x.title' => [
                'label' => 'Title',
                'type'  => 'string',
            ],
            'x.email' => [
                'label' => 'Email',
                'type'  => 'email',
            ],
            'x.mobile' => [
                'label' => 'Mobile',
                'type'  => 'string',
            ],
            'x.points' => [
                'label' => 'Points',
                'type'  => 'float',
            ],
            'x.date' => [
                'label' => 'Date',
                'type'  => 'date',
            ],
            'x.web' => [
                'label' => 'Website',
                'type'  => 'url',
            ],
        ];

        $columns = $fieldsBuilder->getLeadFieldsColumns('x');
        $this->assertSame($expected, $columns);

        $columns = $fieldsBuilder->getLeadFieldsColumns('x.');
        $this->assertSame($expected, $columns);
    }

    public function testGetLeadFilter()
    {
        $fieldModel = $this->getMockBuilder(FieldModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $listModel = $this->getMockBuilder(ListModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fieldModel->expects($this->once())
        ->method('getLeadFields')
            ->with()
            ->willReturn($this->getFields());

        $userSegments = [
            [
                'id'    => 1,
                'name'  => 'United States',
                'alias' => 'us',
            ],
            [
                'id'    => 2,
                'name'  => 'Segment with 3 filters',
                'alias' => 'segment-with-3-filters',
            ],
            [
                'id'    => 3,
                'name'  => 'Segment with 3 filters',
                'alias' => 'segment-with-3-filters1',
            ],
        ];

        $listModel->expects($this->once())
            ->method('getUserLists')
            ->with()
            ->willReturn($userSegments);

        $users = [
            0 => ['id' => 1, 'firstName' => 'John', 'lastName' => 'Doe'],
            1 => ['id' => 2, 'firstName' => 'Joe', 'lastName' => 'Smith'],
        ];

        $userModel->expects($this->once())
            ->method('getUserList')
            ->with()
            ->willReturn($users);

        $fieldsBuilder = new FieldsBuilder($fieldModel, $listModel, $userModel);

        $expected = [
            'l.id' => [
                'label' => 'milex.lead.report.contact_id',
                'type'  => 'int',
                'link'  => 'milex_contact_action',
            ],
            'i.ip_address' => [
                'label' => 'milex.core.ipaddress',
                'type'  => 'text',
            ],
            'l.date_identified' => [
                'label'          => 'milex.lead.report.date_identified',
                'type'           => 'datetime',
                'groupByFormula' => 'DATE(l.date_identified)',
            ],
            'l.points' => [
                'label' => 'milex.lead.points',
                'type'  => 'int',
            ],
            'l.owner_id' => [
                'label' => 'milex.lead.report.owner_id',
                'type'  => 'int',
                'link'  => 'milex_user_action',
            ],
            'u.first_name' => [
                'label' => 'milex.lead.report.owner_firstname',
                'type'  => 'string',
            ],
            'u.last_name' => [
                'label' => 'milex.lead.report.owner_lastname',
                'type'  => 'string',
            ],
            'x.title' => [
                'label' => 'Title',
                'type'  => 'string',
            ],
            'x.email' => [
                'label' => 'Email',
                'type'  => 'email',
            ],
            'x.mobile' => [
                'label' => 'Mobile',
                'type'  => 'string',
            ],
            'x.points' => [
                'label' => 'Points',
                'type'  => 'float',
            ],
            'x.date' => [
                'label' => 'Date',
                'type'  => 'date',
            ],
            'x.web' => [
                'label' => 'Website',
                'type'  => 'url',
            ],
            'segment.leadlist_id' => [
                'alias' => 'segment_id',
                'label' => 'milex.core.filter.lists',
                'type'  => 'select',
                'list'  => [
                    1 => 'United States',
                    2 => 'Segment with 3 filters',
                    3 => 'Segment with 3 filters',
                ],
                'operators' => [
                    'eq' => 'milex.core.operator.equals',
                ],
            ],
            'x.owner_id' => [
                'label' => 'milex.lead.list.filter.owner',
                'type'  => 'select',
                'list'  => [
                    1 => 'John Doe',
                    2 => 'Joe Smith',
                ],
            ],
        ];

        $columns = $fieldsBuilder->getLeadFilter('x', 'segment');
        $this->assertSame($expected, $columns);
    }

    public function testGetCompanyColumns()
    {
        $fieldModel = $this->getMockBuilder(FieldModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $listModel = $this->getMockBuilder(ListModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userModel = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fieldModel->expects($this->exactly(2)) //We have 2 asserts
        ->method('getCompanyFields')
            ->with()
            ->willReturn($this->getFields());

        $fieldsBuilder = new FieldsBuilder($fieldModel, $listModel, $userModel);

        $expected = [
            'comp.id' => [
                'label' => 'milex.lead.report.company.company_id',
                'type'  => 'int',
                'link'  => 'milex_company_action',
            ],
            'comp.companyname' => [
                'label' => 'milex.lead.report.company.company_name',
                'type'  => 'string',
                'link'  => 'milex_company_action',
            ],
            'comp.companycity' => [
                'label' => 'milex.lead.report.company.company_city',
                'type'  => 'string',
                'link'  => 'milex_company_action',
            ],
            'comp.companystate' => [
                'label' => 'milex.lead.report.company.company_state',
                'type'  => 'string',
                'link'  => 'milex_company_action',
            ],
            'comp.companycountry' => [
                'label' => 'milex.lead.report.company.company_country',
                'type'  => 'string',
                'link'  => 'milex_company_action',
            ],
            'comp.companyindustry' => [
                'label' => 'milex.lead.report.company.company_industry',
                'type'  => 'string',
                'link'  => 'milex_company_action',
            ],
            'x.title' => [
                'label' => 'Title',
                'type'  => 'string',
            ],
            'x.email' => [
                'label' => 'Email',
                'type'  => 'email',
            ],
            'x.mobile' => [
                'label' => 'Mobile',
                'type'  => 'string',
            ],
            'x.points' => [
                'label' => 'Points',
                'type'  => 'float',
            ],
            'x.date' => [
                'label' => 'Date',
                'type'  => 'date',
            ],
            'x.web' => [
                'label' => 'Website',
                'type'  => 'url',
            ],
        ];

        $columns = $fieldsBuilder->getCompanyFieldsColumns('x');
        $this->assertSame($expected, $columns);

        $columns = $fieldsBuilder->getCompanyFieldsColumns('x.');
        $this->assertSame($expected, $columns);
    }

    /**
     * @return array
     */
    private function getFields()
    {
        $titleField = new Field();
        $titleField->setLabel('Title');
        $titleField->setAlias('title');
        $titleField->setType('string');

        $emailField = new Field();
        $emailField->setLabel('Email');
        $emailField->setAlias('email');
        $emailField->setType('email');

        $mobileField = new Field();
        $mobileField->setLabel('Mobile');
        $mobileField->setAlias('mobile');
        $mobileField->setType('tel');

        $pointField = new Field();
        $pointField->setLabel('Points');
        $pointField->setAlias('points');
        $pointField->setType('number');

        $dateField = new Field();
        $dateField->setLabel('Date');
        $dateField->setAlias('date');
        $dateField->setType('date');

        $webField = new Field();
        $webField->setLabel('Website');
        $webField->setAlias('web');
        $webField->setType('url');

        return [
            $titleField,
            $emailField,
            $mobileField,
            $pointField,
            $dateField,
            $webField,
        ];
    }
}
