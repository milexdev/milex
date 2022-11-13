<?php

namespace Milex\LeadBundle\Tests\Helper;

use Milex\LeadBundle\Helper\IdentifyCompanyHelper;
use Milex\LeadBundle\Model\CompanyModel;

class IdentifyCompanyHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testDomainExistsRealDomain()
    {
        $helper     = new IdentifyCompanyHelper();
        $reflection = new \ReflectionClass(IdentifyCompanyHelper::class);
        $method     = $reflection->getMethod('domainExists');
        $method->setAccessible(true);
        $result = $method->invokeArgs($helper, ['hello@milex.org']);

        $this->assertTrue(is_string($result));
        $this->assertGreaterThan(0, strlen($result));
    }

    public function testDomainExistsWithFakeDomain()
    {
        $helper     = new IdentifyCompanyHelper();
        $reflection = new \ReflectionClass(IdentifyCompanyHelper::class);
        $method     = $reflection->getMethod('domainExists');
        $method->setAccessible(true);
        $result = $method->invokeArgs($helper, ['hello@domain.fake']);

        $this->assertFalse($result);
    }

    public function testFindCompanyByName()
    {
        $company = [
            'company' => 'Milex',
        ];

        $expected = [
            'companyname'    => 'Milex',
        ];

        $model = $this->getMockBuilder(CompanyModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $model->expects($this->once())
            ->method('checkForDuplicateCompanies')
            ->willReturn([]);

        $model->expects($this->any())
            ->method('fetchCompanyFields')
            ->willReturn([['alias' => 'companyname']]);

        $helper     = new IdentifyCompanyHelper();
        $reflection = new \ReflectionClass(IdentifyCompanyHelper::class);
        $method     = $reflection->getMethod('findCompany');
        $method->setAccessible(true);
        [$resultCompany, $entities] = $method->invokeArgs($helper, [$company, $model]);

        $this->assertEquals($expected, $resultCompany);
    }

    public function testFindCompanyByNameWithValidEmail()
    {
        $company = [
            'company'      => 'Milex',
            'companyemail' => 'hello@milex.org',
        ];

        $expected = [
            'companyname'    => 'Milex',
            'companyemail'   => 'hello@milex.org',
        ];

        $model = $this->getMockBuilder(CompanyModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $model->expects($this->once())
            ->method('checkForDuplicateCompanies')
            ->willReturn([]);

        $model->expects($this->any())
            ->method('fetchCompanyFields')
            ->willReturn([['alias' => 'companyname']]);

        $helper     = new IdentifyCompanyHelper();
        $reflection = new \ReflectionClass(IdentifyCompanyHelper::class);
        $method     = $reflection->getMethod('findCompany');
        $method->setAccessible(true);
        list($resultCompany, $entities) = $method->invokeArgs($helper, [$company, $model]);

        $this->assertEquals($expected, $resultCompany);
    }

    public function testFindCompanyByNameWithValidEmailAndCustomWebsite()
    {
        $company = [
            'company'        => 'Milex',
            'companyemail'   => 'hello@milex.org',
            'companywebsite' => 'https://milex.org',
        ];

        $expected = [
            'companyname'    => 'Milex',
            'companywebsite' => 'https://milex.org',
            'companyemail'   => 'hello@milex.org',
        ];

        $model = $this->getMockBuilder(CompanyModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $model->expects($this->once())
            ->method('checkForDuplicateCompanies')
            ->willReturn([]);

        $model->expects($this->any())
            ->method('fetchCompanyFields')
            ->willReturn([['alias' => 'companyname']]);

        $helper     = new IdentifyCompanyHelper();
        $reflection = new \ReflectionClass(IdentifyCompanyHelper::class);
        $method     = $reflection->getMethod('findCompany');
        $method->setAccessible(true);
        list($resultCompany, $entities) = $method->invokeArgs($helper, [$company, $model]);

        $this->assertEquals($expected, $resultCompany);
    }
}
