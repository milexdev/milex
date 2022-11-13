<?php

declare(strict_types=1);

namespace Milex\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\SkipMigration;
use Milex\CoreBundle\Doctrine\AbstractMilexMigration;

final class Version20200924080139 extends AbstractMilexMigration
{
    /**
     * @throws SkipMigrationException
     */
    public function preUp(Schema $schema): void
    {
        $table = $schema->getTable($this->prefix.'notifications');
        if (512 === $table->getColumn('header')->getLength()) {
            throw new SkipMigration('Schema includes this migration');
        }
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE {$this->prefix}notifications MODIFY header VARCHAR(512) DEFAULT NULL");
    }
}
