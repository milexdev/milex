<?php

namespace Milex\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Milex\CoreBundle\Doctrine\AbstractMilexMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20200220172041 extends AbstractMilexMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `{$this->prefix}categories` SET bundle = 'messages' WHERE bundle = '0';");
    }
}
