<?php

declare(strict_types=1);

namespace Milex\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Milex\CoreBundle\Doctrine\AbstractMilexMigration;

final class Version20210520100503 extends AbstractMilexMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `{$this->prefix}email_stats` CHANGE COLUMN `tokens` `tokens` LONGTEXT CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_unicode_ci' NULL DEFAULT NULL COMMENT '(DC2Type:array)';");
    }
}
