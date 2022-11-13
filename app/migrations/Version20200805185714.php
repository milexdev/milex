<?php

declare(strict_types=1);

namespace Milex\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\Migrations\Exception\SkipMigration;
use Milex\CoreBundle\Doctrine\AbstractMilexMigration;

final class Version20200805185714 extends AbstractMilexMigration
{
    /**
     * @throws SkipMigration|SchemaException
     */
    public function preUp(Schema $schema): void
    {
        $tweetsTable        = $schema->getTable($this->prefix.'tweets');

        if (!$tweetsTable->hasIndex('tweet_text_index')) {
            throw new SkipMigration('Schema includes this migration');
        }
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX '.$this->prefix.'tweet_text_index ON '.$this->prefix.'tweets');
        $this->addSql('ALTER TABLE '.$this->prefix.'tweets CHANGE text text VARCHAR(280)');
    }
}
