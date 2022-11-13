<?php

declare(strict_types=1);

namespace Milex\ConfigBundle\Tests\Controller;

use Milex\ConfigBundle\Model\SysinfoModel;
use Milex\CoreBundle\Test\MilexMysqlTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;

class SysinfoControllerTest extends MilexMysqlTestCase
{
    public function testDbInfoIsShown(): void
    {
        /** @var SysinfoModel */
        $sysinfoModel = self::$container->get('milex.config.model.sysinfo');
        $dbInfo       = $sysinfoModel->getDbInfo();

        // Request sysinfo page
        $crawler = $this->client->request(Request::METHOD_GET, '/s/sysinfo');
        Assert::assertTrue($this->client->getResponse()->isOk());

        $dbVersion       = $crawler->filterXPath("//td[@id='dbinfo-version']")->text();
        $dbDriver        = $crawler->filterXPath("//td[@id='dbinfo-driver']")->text();
        $dbPlatform      = $crawler->filterXPath("//td[@id='dbinfo-platform']")->text();
        $recommendations = $crawler->filter('#recommendations');

        Assert::assertSame($dbInfo['version'], $dbVersion);
        Assert::assertSame($dbInfo['driver'], $dbDriver);
        Assert::assertSame($dbInfo['platform'], $dbPlatform);
        Assert::assertGreaterThan(0, $recommendations->count());
    }
}
