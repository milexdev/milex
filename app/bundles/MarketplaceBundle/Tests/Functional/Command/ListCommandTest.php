<?php

declare(strict_types=1);

namespace Milex\MarketplaceBundle\Tests\Functional\Command;

use Milex\CoreBundle\Test\AbstractMilexTestCase;
use Milex\MarketplaceBundle\Api\Connection;
use Milex\MarketplaceBundle\Command\ListCommand;
use Milex\MarketplaceBundle\DTO\Allowlist as DTOAllowlist;
use Milex\MarketplaceBundle\Service\Allowlist;
use Milex\MarketplaceBundle\Service\PluginCollector;
use PHPUnit\Framework\Assert;

final class ListCommandTest extends AbstractMilexTestCase
{
    public function testCommand(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('getPlugins')
            ->willReturn(json_decode(file_get_contents(__DIR__.'/../../ApiResponse/list.json'), true));

        $allowlist = $this->createMock(Allowlist::class);
        $allowlist->method('getAllowlist')->willReturn(null);

        $pluginCollector = new PluginCollector($connection, $allowlist);
        $command         = new ListCommand($pluginCollector);

        $result = $this->testSymfonyCommand(
            ListCommand::NAME,
            [
                '--page'   => 1,
                '--limit'  => 5,
                '--filter' => 'milex',
            ],
            $command
        );

        $expected = <<<EOF
        +--------------------------------------------------------+-----------+--------+
        | name                                                   | downloads | favers |
        +--------------------------------------------------------+-----------+--------+
        | milex/milex-saelos-bundle                            | 10586     | 11     |
        | koco/milex-recaptcha-bundle                           | 2012      | 20     |
        |     This plugin brings reCAPTCHA integration to        |           |        |
        |     milex.                                            |           |        |
        | monogramm/milex-ldap-auth-bundle                      | 307       | 8      |
        |     This plugin enables LDAP authentication for        |           |        |
        |     milex.                                            |           |        |
        | maatoo/milex-referrals-bundle                         | 527       | 5      |
        |     This plugin enables referrals in milex.           |           |        |
        | thedmsgroup/milex-do-not-contact-extras-bundle        | 532       | 9      |
        |     Adds custom DNC list items to be added to standard |           |        |
        |     Milex DNC lists and creates phpne and sms         |           |        |
        |     channels                                           |           |        |
        +--------------------------------------------------------+-----------+--------+
        Total packages: 58
        Execution time:
        EOF;

        Assert::assertStringContainsString($expected, $result->getDisplay());
        Assert::assertSame(0, $result->getStatusCode());
    }

    public function testCommmandWithAllowlist(): void
    {
        $page  = 1;
        $limit = 5;
        $query = 'milex';

        $plugin1 = <<<EOF
        {
            "results": [
                {
                    "name": "koco\/milex-recaptcha-bundle",
                    "description": "This plugin brings reCAPTCHA integration to milex.",
                    "url": "https:\/\/packagist.org\/packages\/koco\/milex-recaptcha-bundle",
                    "repository": "https:\/\/github.com\/KonstantinCodes\/milex-recaptcha",
                    "downloads": 2012,
                    "favers": 20
                }
            ]
        }
        EOF;

        $plugin2 = <<<EOF
        {
            "results": [
                {
                    "name": "maatoo\/milex-referrals-bundle",
                    "description": "This plugin enables referrals in milex.",
                    "url": "https:\/\/packagist.org\/packages\/maatoo\/milex-referrals-bundle",
                    "repository": "https:\/\/github.com\/maatoo-io\/MilexReferralsBundle",
                    "downloads": 527,
                    "favers": 5
                }
            ]
        }
        EOF;

        $connection = $this->createMock(Connection::class);

        $connection->method('getPlugins')
            ->withConsecutive(
                [1, 1, 'koco/milex-recaptcha-bundle'],
                [1, 1, 'maatoo/milex-referrals-bundle'])
            ->willReturnOnConsecutiveCalls(
                json_decode($plugin1, true),
                json_decode($plugin2, true)
            );

        $allowlistPayload = DTOAllowlist::fromArray(json_decode(file_get_contents(__DIR__.'/../../ApiResponse/allowlist.json'), true));
        $allowlist        = $this->createMock(Allowlist::class);
        $allowlist->method('getAllowList')->willReturn($allowlistPayload);

        $pluginCollector = new PluginCollector($connection, $allowlist);
        $command         = new ListCommand($pluginCollector);

        $result = $this->testSymfonyCommand(
            ListCommand::NAME,
            [
                '--page'   => $page,
                '--limit'  => $limit,
                '--filter' => $query,
            ],
            $command
        );

        $expected = <<<EOF
        +-------------------------------------------------+-----------+--------+
        | name                                            | downloads | favers |
        +-------------------------------------------------+-----------+--------+
        | koco/milex-recaptcha-bundle                    | 2012      | 20     |
        |     This plugin brings reCAPTCHA integration to |           |        |
        |     milex.                                     |           |        |
        | maatoo/milex-referrals-bundle                  | 527       | 5      |
        |     This plugin enables referrals in milex.    |           |        |
        +-------------------------------------------------+-----------+--------+
        Total packages: 2
        Execution time:
        EOF;

        Assert::assertStringContainsString($expected, $result->getDisplay());
        Assert::assertSame(0, $result->getStatusCode());
    }
}
