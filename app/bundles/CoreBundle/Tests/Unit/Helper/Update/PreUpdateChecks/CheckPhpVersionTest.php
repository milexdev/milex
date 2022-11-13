<?php

namespace Milex\CoreBundle\Tests\Unit\Helper\Update\PreUpdateChecks;

use Milex\CoreBundle\Helper\Update\PreUpdateChecks\CheckPhpVersion;
use Milex\CoreBundle\Release\Metadata;
use Milex\CoreBundle\Test\MilexMysqlTestCase;

class CheckPhpVersionTest extends MilexMysqlTestCase
{
    public function testPhpVersionOk(): void
    {
        $releaseMetadata = [
            'version'                           => '10.0.1',
            'stability'                         => 'stable',
            'minimum_php_version'               => '7.4.0', // Our CI is always this or a higher version so we're good
            'maximum_php_version'               => '999.99.99', // Hopefully this version will never exist
            'show_php_version_warning_if_under' => '7.4.0',
            'minimum_milex_version'            => '3.2.0',
            'announcement_url'                  => '',
            'minimum_mysql_version'             => '5.6.0',
            'minimum_mariadb_version'           => '10.1.0',
        ];

        $check = new CheckPhpVersion();
        $check->setUpdateCandidateMetadata(new Metadata($releaseMetadata));
        $result = $check->runCheck();

        // Just checking if we can properly detect the PHP version
        $this->assertSame(true, $result->success);
    }
}
