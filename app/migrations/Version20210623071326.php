<?php

declare(strict_types=1);

namespace Milex\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Milex\CoreBundle\Doctrine\AbstractMilexMigration;

final class Version20210623071326 extends AbstractMilexMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE {$this->prefix}forms MODIFY post_action_property LONGTEXT ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE {$this->prefix}forms SET post_action_property = left(post_action_property,191)");
        $this->addSql("ALTER TABLE {$this->prefix}forms MODIFY post_action_property VARCHAR(191) DEFAULT NULL ");
    }
}
