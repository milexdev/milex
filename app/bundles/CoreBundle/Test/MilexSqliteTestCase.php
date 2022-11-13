<?php

namespace Milex\CoreBundle\Test;

use Doctrine\ORM\Events;
use Milex\CoreBundle\Test\DoctrineExtensions\TablePrefix;
use Milex\InstallBundle\Helper\SchemaHelper;
use Milex\InstallBundle\InstallFixtures\ORM\LeadFieldData;
use Milex\InstallBundle\InstallFixtures\ORM\RoleData;
use Milex\UserBundle\DataFixtures\ORM\LoadRoleData;
use Milex\UserBundle\DataFixtures\ORM\LoadUserData;

abstract class MilexSqliteTestCase extends AbstractMilexTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (file_exists($this->getOriginalDatabasePath())) {
            $this->createDatabaseFromFile();
        } else {
            $this->createDatabase();
            $this->applyMigrations();
            $this->installDatabaseFixtures([LeadFieldData::class, RoleData::class, LoadRoleData::class, LoadUserData::class]);
            $this->backupOrginalDatabase();
        }
    }

    private function createDatabase()
    {
        // fix problem with prefixes in sqlite
        $tablePrefix = new TablePrefix('prefix_');
        $this->em->getEventManager()->addEventListener(Events::loadClassMetadata, $tablePrefix);

        $dbParams = array_merge(self::$container->get('doctrine')->getConnection()->getParams(), [
            'table_prefix'  => null,
            'backup_tables' => 0,
        ]);

        // create schema
        $schemaHelper = new SchemaHelper($dbParams);
        $schemaHelper->setEntityManager($this->em);

        $schemaHelper->createDatabase();
        $schemaHelper->installSchema();

        $this->em->getConnection()->close();
    }

    private function createDatabaseFromFile()
    {
        copy($this->getOriginalDatabasePath(), $this->getDatabasePath());
    }

    private function backupOrginalDatabase()
    {
        copy($this->getDatabasePath(), $this->getOriginalDatabasePath());
    }

    private function getOriginalDatabasePath()
    {
        return $this->getDatabasePath().'.original';
    }

    private function getDatabasePath()
    {
        return self::$container->get('doctrine')->getConnection()->getParams()['path'];
    }
}
