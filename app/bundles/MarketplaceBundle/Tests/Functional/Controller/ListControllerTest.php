<?php

declare(strict_types=1);

namespace Milex\MarketplaceBundle\Tests\Functional\Controller;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Milex\CoreBundle\Test\MilexMysqlTestCase;
use Milex\MarketplaceBundle\Service\Allowlist;
use Milex\MarketplaceBundle\Service\Config;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ListControllerTest extends MilexMysqlTestCase
{
    protected function setUp(): void
    {
        if ('testMarketplaceListTableWithNoAllowList' === $this->getName()) {
            $this->configParams[Config::MARKETPLACE_ALLOWLIST_URL] = '0'; // Empty string results in null for some reason.
        }

        parent::setUp();
    }

    public function testMarketplaceListTableWithNoAllowList(): void
    {
        /** @var MockHandler $handlerStack */
        $handlerStack = self::$container->get('milex.http.client.mock_handler');
        $handlerStack->append(
            new Response(SymfonyResponse::HTTP_OK, [], file_get_contents(__DIR__.'/../../ApiResponse/list.json'))  // Getting the package list from Packagist API.
        );

        /** @var Allowlist $allowlist */
        $allowlist = self::$container->get('marketplace.service.allowlist');
        $allowlist->clearCache();

        $crawler = $this->client->request('GET', 's/marketplace');

        Assert::assertTrue($this->client->getResponse()->isOk(), $this->client->getResponse()->getContent());

        Assert::assertSame(
            [
                'Milex Saelos Bundle',
                'Milex Recaptcha Bundle',
                'Milex Ldap Auth Bundle',
                'Milex Referrals Bundle',
                'Milex Do Not Contact Extras Bundle',
            ],
            array_map(
                fn (string $dirtyPackageName) => trim($dirtyPackageName),
                $crawler->filter('#marketplace-packages-table .package-name a')->extract(['_text'])
            )
        );
    }

    public function testMarketplaceListTableWithAllowList(): void
    {
        $mockResults = json_decode(file_get_contents(__DIR__.'/../../ApiResponse/list.json'), true)['results'];

        /** @var MockHandler $handlerStack */
        $handlerStack = self::$container->get('milex.http.client.mock_handler');
        $handlerStack->append(
            new Response(SymfonyResponse::HTTP_OK, [], file_get_contents(__DIR__.'/../../ApiResponse/allowlist.json')), // Getting Allow list from Github API.
            new Response(SymfonyResponse::HTTP_OK, [], json_encode(['results' => [$mockResults[1]]])), // milex-recaptcha-bundle
            new Response(SymfonyResponse::HTTP_OK, [], json_encode(['results' => [$mockResults[3]]])), // milex-referrals-bundle
        );

        /** @var Allowlist $allowlist */
        $allowlist = self::$container->get('marketplace.service.allowlist');
        $allowlist->clearCache();

        $crawler = $this->client->request('GET', 's/marketplace');

        Assert::assertTrue($this->client->getResponse()->isOk(), $this->client->getResponse()->getContent());

        Assert::assertSame(
            [
                'KocoCaptcha',
                'Milex Referrals Bundle',
            ],
            array_map(
                fn (string $dirtyPackageName) => trim($dirtyPackageName),
                $crawler->filter('#marketplace-packages-table .package-name a')->extract(['_text'])
            )
        );
    }
}
