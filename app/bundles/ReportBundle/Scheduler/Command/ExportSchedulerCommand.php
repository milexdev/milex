<?php

namespace Milex\ReportBundle\Scheduler\Command;

use Milex\ReportBundle\Exception\FileIOException;
use Milex\ReportBundle\Model\ReportExporter;
use Milex\ReportBundle\Scheduler\Option\ExportOption;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ExportSchedulerCommand extends Command
{
    /**
     * @var ReportExporter
     */
    private $reportExporter;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(ReportExporter $reportExporter, TranslatorInterface $translator)
    {
        parent::__construct();
        $this->reportExporter = $reportExporter;
        $this->translator     = $translator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('milex:reports:scheduler')
            ->setDescription('Processes scheduler for report\'s export')
            ->addOption('--report', 'report', InputOption::VALUE_OPTIONAL, 'ID of report. Process all reports if not set.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $report = $input->getOption('report');

        try {
            $exportOption = new ExportOption($report);
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>'.$this->translator->trans('milex.report.schedule.command.invalid_parameter').'</error>');

            return;
        }

        try {
            $this->reportExporter->processExport($exportOption);

            $output->writeln('<info>'.$this->translator->trans('milex.report.schedule.command.finished').'</info>');
        } catch (FileIOException $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return;
        }
    }
}
