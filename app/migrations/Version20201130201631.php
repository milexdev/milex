<?php

declare(strict_types=1);

namespace Milex\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Milex\CoreBundle\Doctrine\AbstractMilexMigration;

final class Version20201130201631 extends AbstractMilexMigration
{
    public function up(Schema $schema): void
    {
        $confFile = dirname(__DIR__).'/config/local.php';

        if (!file_exists($confFile)) {
            return;
        }

        require $confFile;

        /** @phpstan-ignore-next-line */
        if (isset($parameters) && array_key_exists('db_server_version', $parameters)) {
            // MySQL minimum version was bumped to 5.7 in Milex 3
            // https://github.com/milex/milex/pull/9437
            $parameters['db_server_version'] = '5.7';
        } else {
            return;
        }

        // Write updated config to local.php
        $result = file_put_contents($confFile, "<?php\n".'$parameters = '.var_export($parameters, true).';');

        if (false === $result) {
            throw new \Exception('Couldn\'t update configuration file with new db_server_version value (5.7).');
        }
    }
}
