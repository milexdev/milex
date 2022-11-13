<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Tests\Functional\Services\SyncDataExchange\Internal\ObjectHelper;

use DateTime;
use Milex\CoreBundle\Test\MilexMysqlTestCase;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO as OrderFieldDAO;
use Milex\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use Milex\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object as SyncObject;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectHelper\CompanyObjectHelper;
use Milex\IntegrationsBundle\Sync\SyncDataExchange\MilexSyncDataExchange;
use Milex\LeadBundle\Entity\Company;
use Milex\LeadBundle\Model\CompanyModel;
use Milex\UserBundle\Model\UserModel;
use PHPUnit\Framework\Assert;

class CompanyObjectHelperTest extends MilexMysqlTestCase
{
    public function testUpdateEmpty(): void
    {
        /** @var CompanyObjectHelper $companyObjectHelper */
        $companyObjectHelper  = self::$container->get('milex.integrations.helper.company_object');
        $updatedMappedObjects = $companyObjectHelper->update([], []);
        Assert::assertSame([], $updatedMappedObjects);
    }

    public function testUpdate(): void
    {
        /** @var UserModel $userModel */
        $userModel = self::$container->get('milex.user.model.user');
        $users     = $userModel->getRepository()->findAll();
        $user      = reset($users);
        $now       = new DateTime();

        $company1 = new Company();
        $company1->setDateAdded($now);
        $company1->setOwner($user);

        $company2 = new Company();
        $company2->setDateAdded($now);
        $company2->setOwner($user);

        /** @var CompanyModel $companyModel */
        $companyModel = self::$container->get('milex.lead.model.company');
        $companyModel->saveEntity($company1);
        $companyModel->saveEntity($company2);

        $phone = '123456789';
        $city  = 'Boston';

        /** @var CompanyObjectHelper $companyObjectHelper */
        $companyObjectHelper = self::$container->get('milex.integrations.helper.company_object');
        $companyObjectHelper->update([
            $company1->getId(),
            $company2->getId(),
        ], [
            $company1->getId() => $this->buildObjectChangeDAO($company1, 'companyphone', $phone),
            $company2->getId() => $this->buildObjectChangeDAO($company2, 'companycity', $city),
        ]);

        Assert::assertSame($phone, $company1->getPhone());
        Assert::assertSame($city, $company2->getCity());
    }

    private function buildObjectChangeDAO(Company $company, string $name, string $value): ObjectChangeDAO
    {
        $objectChangeDAO = new ObjectChangeDAO('Test', MilexSyncDataExchange::OBJECT_COMPANY, $company->getId(), SyncObject\Company::NAME, $company->getId(), new DateTime());
        $objectChangeDAO->addField(new OrderFieldDAO($name, new NormalizedValueDAO(NormalizedValueDAO::PHONE_TYPE, $value)));

        return $objectChangeDAO;
    }
}
