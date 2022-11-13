<?php

declare(strict_types=1);

namespace MilexPlugin\MilexCrmBundle\Command;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\Templating\Helper\TranslatorHelper;
use Milex\LeadBundle\Entity\Company;
use Milex\LeadBundle\Entity\Lead;
use Milex\PluginBundle\Helper\IntegrationHelper;
use MilexPlugin\MilexCrmBundle\Integration\Pipedrive\Export\CompanyExport;
use MilexPlugin\MilexCrmBundle\Integration\Pipedrive\Export\LeadExport;
use MilexPlugin\MilexCrmBundle\Integration\PipedriveIntegration;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PushDataToPipedriveCommand extends ContainerAwareCommand
{
    private SymfonyStyle $io;
    private IntegrationHelper $integrationHelper;
    private TranslatorHelper $translatorHelper;
    private EntityManager $entityManager;
    private CompanyExport $companyExport;
    private LeadExport $leadExport;

    public function __construct(
        IntegrationHelper $integrationHelper,
        TranslatorHelper $translatorHelper,
        EntityManager $entityManager,
        CompanyExport $companyExport,
        LeadExport $leadExport
    ) {
        $this->integrationHelper = $integrationHelper;
        $this->translatorHelper  = $translatorHelper;
        $this->entityManager     = $entityManager;
        $this->companyExport     = $companyExport;
        $this->leadExport        = $leadExport;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('milex:integration:pipedrive:push')
            ->setDescription('Pushes the data from Milex to Pipedrive')
            ->addOption(
                '--restart',
                null,
                InputOption::VALUE_NONE,
                'Restart intgeration'
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var PipeDriveIntegration $integrationObject */
        $integrationObject = $this->integrationHelper
            ->getIntegrationObject(PipedriveIntegration::INTEGRATION_NAME);
        $this->io          = new SymfonyStyle($input, $output);

        $pushed = 0;

        if (!$integrationObject || !$integrationObject->getIntegrationSettings()->getIsPublished()) {
            $this->io->note('Pipedrive integration is disabled.');

            return;
        }

        if ($input->getOption('restart')) {
            $this->io->note(
                $this->translatorHelper->trans(
                    'milex.plugin.config.integration.restarted',
                    ['%integration%' => $integrationObject->getName()]
                )
            );
            $integrationObject->removeIntegrationEntities();
        }

        if ($integrationObject->isCompanySupportEnabled()) {
            $this->io->title('Pushing Companies');
            $this->companyExport->setIntegration($integrationObject);

            $companies = $this->entityManager->getRepository(Company::class)->findAll();
            foreach ($companies as $company) {
                if ($this->companyExport->pushCompany($company)) {
                    ++$pushed;
                }
            }
            $this->io->text('Pushed '.$pushed);
        }

        $leads = $this->entityManager->getRepository(Lead::class)->findAll();
        $this->io->title('Pushing Leads');
        $this->leadExport->setIntegration($integrationObject);
        $pushed = 0;
        foreach ($leads as $lead) {
            if ($this->leadExport->create($lead)) {
                ++$pushed;
            }
        }
        $this->io->text('Pushed '.$pushed);

        $this->io->success('Execution time: '.number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 3));
    }
}
