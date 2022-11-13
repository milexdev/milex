<?php

namespace Milex\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Milex\CoreBundle\Doctrine\AbstractMilexMigration;

/**
 * Migration.
 */
class Version20200227110431 extends AbstractMilexMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX '.$this->prefix.'dnc_channel_id_search ON '.$this->prefix.'lead_donotcontact (channel_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX '.$this->prefix.'dnc_channel_id_search ON '.$this->prefix.'lead_donotcontact');
    }
}
