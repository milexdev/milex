<?php

declare(strict_types=1);

namespace Milex\CoreBundle\Tests\Command;

use Exception;
use Milex\CoreBundle\Entity\IpAddress;
use Milex\CoreBundle\Entity\IpAddressRepository;
use Milex\CoreBundle\Test\MilexMysqlTestCase;

class UnusedIpDeleteCommandFunctionalTest extends MilexMysqlTestCase
{
    /**
     * @throws Exception
     */
    public function testUnusedIpDeleteCommand(): void
    {
        // Emulate unused IP address.
        /** @var IpAddressRepository $ipAddressRepo */
        $ipAddressRepo = $this->em->getRepository(IpAddress::class);
        $ipAddressRepo->saveEntity(new IpAddress('127.0.0.1'));
        $count = $ipAddressRepo->count(['ipAddress' => '127.0.0.1']);
        self::assertSame(1, $count);

        // Delete unused IP address.
        $this->runCommand('milex:unusedip:delete');

        $count = $ipAddressRepo->count(['ipAddress' => '127.0.0.1']);
        self::assertSame(0, $count);
    }
}
