<?php

namespace Milex\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI Command to generate production assets.
 */
class GenerateProductionAssetsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('milex:assets:generate')
            ->setDescription('Combines and minifies asset files from each bundle into single production files')
            ->setHelp(
                <<<'EOT'
                The <info>%command.name%</info> command Combines and minifies files from each bundle's Assets/css/* and Assets/js/* folders into single production files stored in root/media/css and root/media/js respectively.

<info>php %command.full_name%</info>
EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container   = $this->getContainer();
        $assetHelper = $container->get('milex.helper.assetgeneration');

        $pathsHelper = $container->get('milex.helper.paths');

        // Combine and minify bundle assets
        $assetHelper->getAssets(true);

        // Minify Milex Form SDK
        file_put_contents(
            $pathsHelper->getSystemPath('assets', true).'/js/milex-form-tmp.js',
            \Minify::combine([$pathsHelper->getSystemPath('assets', true).'/js/milex-form-src.js'])
        );
        // Fix the MilexSDK loader
        file_put_contents(
            $pathsHelper->getSystemPath('assets', true).'/js/milex-form.js',
            str_replace("'milex-form-src.js'", "'milex-form.js'",
                file_get_contents($pathsHelper->getSystemPath('assets', true).'/js/milex-form-tmp.js'))
        );
        // Remove temp file.
        unlink($pathsHelper->getSystemPath('assets', true).'/js/milex-form-tmp.js');

        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $container->get('translator');
        $translator->setLocale($container->get('milex.helper.core_parameters')->get('locale'));

        // Update successful
        $output->writeln('<info>'.$translator->trans('milex.core.command.asset_generate_success').'</info>');

        return 0;
    }
}
