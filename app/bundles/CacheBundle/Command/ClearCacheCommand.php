<?php

declare(strict_types=1);

namespace Milex\CacheBundle\Command;

use Milex\CacheBundle\Cache\CacheProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI Command to clear the application cache.
 */
class ClearCacheCommand extends ContainerAwareCommand
{
    protected function configure(): void
    {
        $this->setName('milex:cache:clear')
            ->setDescription('Clears Milex\'s cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        /** @var CacheProvider $cacheProvider */
        $cacheProvider = $this->getContainer()->get('milex.cache.provider');

        return (int) !$cacheProvider->clear();
    }
}
