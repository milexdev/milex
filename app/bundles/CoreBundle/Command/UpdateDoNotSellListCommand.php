<?php

namespace Milex\CoreBundle\Command;

use Milex\CoreBundle\Helper\MaxMindDoNotSellDownloadHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\TranslatorInterface;

class UpdateDoNotSellListCommand extends Command
{
    /**
     * @var MaxMindDoNotSellDownloadHelper
     */
    private $maxMindDoNotSellDownloadHelper;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(MaxMindDoNotSellDownloadHelper $maxMindDoNotSellDownloadHelper, TranslatorInterface $translator)
    {
        parent::__construct();
        $this->maxMindDoNotSellDownloadHelper = $maxMindDoNotSellDownloadHelper;
        $this->translator                     = $translator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('milex:donotsell:download')
            ->setDescription('Fetch remote do not sell list from MaxMind')
            ->setHelp(
                <<<'EOT'
                The <info>%command.name%</info> command is used to update MaxMind Do Not Sell list.

<info>php %command.full_name%</info>
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->maxMindDoNotSellDownloadHelper->downloadRemoteDataStore()) {
            $output->writeln('<info>'.$this->translator->trans('milex.core.success').'</info>');
        } else {
            $remoteUrl = $this->maxMindDoNotSellDownloadHelper->getRemoteDataStoreDownloadUrl();
            $localPath = $this->maxMindDoNotSellDownloadHelper->getLocalDataStoreFilepath();

            if ($remoteUrl && $localPath) {
                $output->writeln('<error>'.$this->translator->trans(
                        'milex.core.do_not_sell.remote_fetch_error',
                        [
                            '%remoteUrl%' => $remoteUrl,
                            '%localPath%' => $localPath,
                        ]
                    ).'</error>');
            } else {
                $output->writeln('<error>'.$this->translator->trans(
                        'milex.core.do_not_sell.remote_fetch_error_generic'
                    ).'</error>');
            }
        }

        return 0;
    }
}
