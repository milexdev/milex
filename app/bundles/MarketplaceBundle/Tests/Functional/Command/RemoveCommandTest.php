<?php

declare(strict_types=1);

namespace Milex\MarketplaceBundle\Tests\Functional\Command;

use Milex\CoreBundle\Helper\ComposerHelper;
use Milex\CoreBundle\Test\AbstractMilexTestCase;
use Milex\MarketplaceBundle\Command\RemoveCommand;
use Milex\MarketplaceBundle\Model\ConsoleOutputModel;
use PHPUnit\Framework\Assert;
use Psr\Log\LoggerInterface;

final class RemoveCommandTest extends AbstractMilexTestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&LoggerInterface
     */
    private $logger;
    private string $packageName;

    public function setUp(): void
    {
        parent::setUp();
        $this->logger      = $this->createMock(LoggerInterface::class);
        $this->packageName = 'koco/milex-recaptcha-bundle';
    }

    public function testRemoveCommand(): void
    {
        $composer    = $this->createMock(ComposerHelper::class);
        $composer->method('remove')
            ->with($this->packageName)
            ->willReturn(new ConsoleOutputModel(0, 'OK'));
        $composer->method('getMilexPluginPackages')
            ->willReturn(['koco/milex-recaptcha-bundle']);
        $command = new RemoveCommand($composer, $this->logger);

        $result = $this->testSymfonyCommand(
            'milex:marketplace:remove',
            ['package' => $this->packageName],
            $command
        );

        Assert::assertSame(0, $result->getStatusCode());
    }

    public function testRemoveCommandWithInvalidPackageType(): void
    {
        $composer    = $this->createMock(ComposerHelper::class);
        $composer->method('remove')
            ->with($this->packageName)
            ->willReturn(new ConsoleOutputModel(0, 'OK'));
        $composer->method('getMilexPluginPackages')
            ->willReturn([]);
        $command = new RemoveCommand($composer, $this->logger);

        $result = $this->testSymfonyCommand(
            'milex:marketplace:remove',
            ['package' => $this->packageName],
            $command
        );

        Assert::assertSame(1, $result->getStatusCode());
    }

    public function testRemoveCommandWithComposerError(): void
    {
        $composer    = $this->createMock(ComposerHelper::class);
        $composer->method('remove')
            ->with($this->packageName)
            ->willReturn(new ConsoleOutputModel(1, 'Error while removing package'));
        $composer->method('getMilexPluginPackages')
            ->willReturn([]);
        $command = new RemoveCommand($composer, $this->logger);

        $result = $this->testSymfonyCommand(
            'milex:marketplace:remove',
            ['package' => $this->packageName],
            $command
        );

        Assert::assertSame(1, $result->getStatusCode());
    }
}
