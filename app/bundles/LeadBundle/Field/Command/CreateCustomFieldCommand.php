<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Field\Command;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Schema\SchemaException;
use Milex\LeadBundle\Entity\LeadFieldRepository;
use Milex\LeadBundle\Field\BackgroundService;
use Milex\LeadBundle\Field\Exception\AbortColumnCreateException;
use Milex\LeadBundle\Field\Exception\ColumnAlreadyCreatedException;
use Milex\LeadBundle\Field\Exception\CustomFieldLimitException;
use Milex\LeadBundle\Field\Exception\LeadFieldWasNotFoundException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CreateCustomFieldCommand extends ContainerAwareCommand
{
    /**
     * @var BackgroundService
     */
    private $backgroundService;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LeadFieldRepository
     */
    private $leadFieldRepository;

    public function __construct(
        BackgroundService $backgroundService,
        TranslatorInterface $translator,
        LeadFieldRepository $leadFieldRepository
    ) {
        parent::__construct();
        $this->backgroundService   = $backgroundService;
        $this->translator          = $translator;
        $this->leadFieldRepository = $leadFieldRepository;
    }

    public function configure(): void
    {
        parent::configure();

        $this->setName('milex:custom-field:create-column')
            ->setDescription('Create custom field column in the background')
            ->addOption('--id', '-i', InputOption::VALUE_REQUIRED, 'LeadField ID.')
            ->addOption('--user', '-u', InputOption::VALUE_OPTIONAL, 'User ID - User which receives a notification.')
            ->setHelp(
                <<<'EOT'
The <info>%command.name%</info> command will create a column in a lead_fields table if the proces should run in background.

<info>php %command.full_name%</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $leadFieldId = (int) $input->getOption('id');
        $userId      = (int) $input->getOption('user');

        if (!$leadFieldId) {
            $leadField = $this->leadFieldRepository->getFieldThatIsMissingColumn();

            if (!$leadField) {
                $output->writeln('<info>'.$this->translator->trans('milex.lead.field.all_fields_have_columns').'</info>');

                return 0;
            }

            $leadFieldId = $leadField->getId();
            $userId      = $leadField->getCreatedBy();
        }

        try {
            $this->backgroundService->addColumn($leadFieldId, $userId);
        } catch (LeadFieldWasNotFoundException $e) {
            $output->writeln('<error>'.$this->translator->trans('milex.lead.field.notfound').'</error>');

            return 1;
        } catch (ColumnAlreadyCreatedException $e) {
            $output->writeln('<error>'.$this->translator->trans('milex.lead.field.column_already_created').'</error>');

            return 0;
        } catch (AbortColumnCreateException $e) {
            $output->writeln('<error>'.$this->translator->trans('milex.lead.field.column_creation_aborted').'</error>');

            return 0;
        } catch (CustomFieldLimitException $e) {
            $output->writeln('<error>'.$this->translator->trans($e->getMessage()).'</error>');

            return 1;
        } catch (DriverException $e) {
            $output->writeln('<error>'.$this->translator->trans($e->getMessage()).'</error>');

            return 1;
        } catch (SchemaException $e) {
            $output->writeln('<error>'.$this->translator->trans($e->getMessage()).'</error>');

            return 1;
        } catch (DBALException $e) {
            $output->writeln('<error>'.$this->translator->trans($e->getMessage()).'</error>');

            return 1;
        } catch (\Milex\CoreBundle\Exception\SchemaException $e) {
            $output->writeln('<error>'.$this->translator->trans($e->getMessage()).'</error>');

            return 1;
        }

        $output->writeln('');
        $output->writeln('<info>'.$this->translator->trans('milex.lead.field.column_was_created', ['%id%' => $leadFieldId]).'</info>');

        return 0;
    }
}
