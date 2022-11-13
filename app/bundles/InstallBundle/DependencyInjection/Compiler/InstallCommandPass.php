<?php

declare(strict_types=1);

namespace Milex\InstallBundle\DependencyInjection\Compiler;

use Milex\InstallBundle\Command\InstallCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class InstallCommandPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $args = $_SERVER['argv'] ?? [];

        if (!in_array(InstallCommand::COMMAND, $args, true)) {
            return;
        }

        $definition = (new InstallCommand())->getDefinition();
        $definition->addOption(
            new InputOption('--verbose', '-v', InputOption::VALUE_NONE, 'Increase verbosity of messages.')
        );
        $definition->addOption(
            new InputOption(
                '--env',
                '-e',
                InputOption::VALUE_REQUIRED,
                'The Environment name.',
                $container->getParameter('kernel.environment')
            )
        );
        $definition->addOption(
            new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.')
        );

        $input       = new ArgvInput($args, $definition);
        $tablePrefix = $input->hasOption('db_table_prefix')
            ? $input->getOption('db_table_prefix')
            : MILEX_TABLE_PREFIX;

        if (!$tablePrefix) {
            return;
        }

        $container->setParameter('milex.db_table_prefix', $tablePrefix);
        $container->getDefinition('milex.tblprefix_subscriber')->setArgument('$tablePrefix', $tablePrefix);
        $container->getDefinition('milex.schema.helper.column')->setArgument('$prefix', $tablePrefix);
        $container->getDefinition('milex.schema.helper.index')->setArgument('$prefix', $tablePrefix);
    }
}
