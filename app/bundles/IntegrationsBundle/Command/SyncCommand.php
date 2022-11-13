<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Command;

use Milex\IntegrationsBundle\Exception\InvalidValueException;
use Milex\IntegrationsBundle\Sync\DAO\Sync\InputOptionsDAO;
use Milex\IntegrationsBundle\Sync\SyncService\SyncServiceInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncCommand extends ContainerAwareCommand
{
    public const NAME = 'milex:integrations:sync';

    /**
     * @var SyncServiceInterface
     */
    private $syncService;

    public function __construct(SyncServiceInterface $syncService)
    {
        parent::__construct();

        $this->syncService = $syncService;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName(self::NAME)
            ->setDescription('Fetch objects from integration.')
            ->addArgument(
                'integration',
                InputOption::VALUE_REQUIRED,
                'Fetch objects from integration.',
                null
            )
            ->addOption(
                '--start-datetime',
                '-t',
                InputOption::VALUE_OPTIONAL,
                'Set start date/time for updated values in UTC timezone.'
            )
            ->addOption(
                '--end-datetime',
                null,
                InputOption::VALUE_OPTIONAL,
                'Set start date/time for updated values in UTC timezone.'
            )
            ->addOption(
                '--milex-object-id',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Provide specific Milex object IDs you want to sync. If some object IDs are provided then the start/end dates have no effect. Example: --milex-object-id=contact:12 --milex-object-id=company:13'
            )
            ->addOption(
                '--integration-object-id',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Provide specific integration object IDs you want to sync. If some object IDs are provided then the start/end dates have no effect. It depends on each integration if this is supported. Example: --integration-object-id=Account:12 --integration-object-id=Lead:13'
            )
            ->addOption(
                '--first-time-sync',
                '-f',
                InputOption::VALUE_NONE,
                'Notate if this is a first time sync where Milex will sync existing objects instead of just tracked changes'
            )
            ->addOption(
                '--disable-push',
                null,
                InputOption::VALUE_NONE,
                'Notate if the sync should execute only pushing items from Milex to the integration'
            )
            ->addOption(
                '--disable-pull',
                null,
                InputOption::VALUE_NONE,
                'Notate if the sync should execute only pulling items from integration to the Milex'
            )
            ->addOption(
                '--option',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Provide option pass to InputOptions Example: --option="type:1" --option="channel_id:1"'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $inputOptions = new InputOptionsDAO(array_merge($input->getArguments(), $input->getOptions()));
        } catch (InvalidValueException $e) {
            $io->error($e->getMessage());

            return 1;
        }

        try {
            defined('MILEX_INTEGRATION_SYNC_IN_PROGRESS') or define('MILEX_INTEGRATION_SYNC_IN_PROGRESS', $inputOptions->getIntegration());

            $this->syncService->processIntegrationSync($inputOptions);
        } catch (\Throwable $e) {
            if ('dev' === $input->getOption('env') || (defined('MILEX_ENV') && MILEX_ENV === 'dev')) {
                throw $e;
            }

            $io->error($e->getMessage());

            return 1;
        }

        $io->success('Execution time: '.number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3));

        return 0;
    }
}
