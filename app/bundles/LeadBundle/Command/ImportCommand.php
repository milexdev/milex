<?php

namespace Milex\LeadBundle\Command;

use Milex\LeadBundle\Exception\ImportDelayedException;
use Milex\LeadBundle\Exception\ImportFailedException;
use Milex\LeadBundle\Helper\Progress;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI Command to import data.
 */
class ImportCommand extends ContainerAwareCommand
{
    public const COMMAND_NAME = 'milex:import';

    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Imports data to Milex')
            ->addOption('--id', '-i', InputOption::VALUE_OPTIONAL, 'Specific ID to import. Defaults to next in the queue.', false)
            ->addOption('--limit', '-l', InputOption::VALUE_OPTIONAL, 'Maximum number of records to import for this script execution.', 0)
            ->setHelp(
                <<<'EOT'
The <info>%command.name%</info> command starts to import CSV files when some are created.

<info>php %command.full_name%</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->getContainer()->get('translator');

        /** @var \Milex\LeadBundle\Model\ImportModel $model */
        $model = $this->getContainer()->get('milex.lead.model.import');

        $progress = new Progress($output);
        $id       = (int) $input->getOption('id');
        $limit    = (int) $input->getOption('limit');

        if ($id) {
            $import = $model->getEntity($id);

            // This specific import was not found
            if (!$import) {
                $output->writeln('<error>'.$translator->trans('milex.core.error.notfound', [], 'flashes').'</error>');

                return 1;
            }
        } else {
            $import = $model->getImportToProcess();

            // No import waiting in the queue. Finish silently.
            if (null === $import) {
                return 0;
            }
        }

        $output->writeln('<info>'.$translator->trans(
            'milex.lead.import.is.starting',
            [
                '%id%'    => $import->getId(),
                '%lines%' => $import->getLineCount(),
            ]
        ).'</info>');

        try {
            $model->beginImport($import, $progress, $limit);
        } catch (ImportFailedException $e) {
            $output->writeln('<error>'.$translator->trans(
                'milex.lead.import.failed',
                [
                    '%reason%' => $import->getStatusInfo(),
                ]
            ).'</error>');

            return 1;
        } catch (ImportDelayedException $e) {
            $output->writeln('<info>'.$translator->trans(
                'milex.lead.import.delayed',
                [
                    '%reason%' => $import->getStatusInfo(),
                ]
            ).'</info>');

            return 0;
        }

        // Success
        $output->writeln('<info>'.$translator->trans(
            'milex.lead.import.result',
            [
                '%lines%'   => $import->getProcessedRows(),
                '%created%' => $import->getInsertedCount(),
                '%updated%' => $import->getUpdatedCount(),
                '%ignored%' => $import->getIgnoredCount(),
                '%time%'    => round(microtime(true) - $start, 2),
            ]
        ).'</info>');

        return 0;
    }
}
