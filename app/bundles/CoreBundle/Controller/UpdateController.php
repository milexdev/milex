<?php

namespace Milex\CoreBundle\Controller;

use Milex\CoreBundle\Helper\CoreParametersHelper;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class UpdateController.
 */
class UpdateController extends CommonController
{
    /**
     * Generates the update view.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        if (!$this->user->isAdmin()) {
            return $this->accessDenied();
        }

        /** @var \Milex\CoreBundle\Helper\UpdateHelper $updateHelper */
        $updateHelper = $this->container->get('milex.helper.update');
        $updateData   = $updateHelper->fetchData();
        /** @var CoreParametersHelper $coreParametersHelper */
        $coreParametersHelper = $this->container->get('milex.helper.core_parameters');

        return $this->delegateView([
            'viewParameters' => [
                'updateData'        => $updateData,
                'currentVersion'    => MILEX_VERSION,
                'isComposerEnabled' => $coreParametersHelper->get('composer_updates', false),
            ],
            'contentTemplate' => 'MilexCoreBundle:Update:index.html.php',
            'passthroughVars' => [
                'milexContent' => 'update',
                'route'         => $this->generateUrl('milex_core_update'),
            ],
        ]);
    }

    /**
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function schemaAction()
    {
        if (!$this->user->isAdmin()) {
            return $this->accessDenied();
        }

        $result       = 0;
        $failed       = false;
        $noMigrations = true;
        $iterator     = new \FilesystemIterator($this->container->getParameter('kernel.root_dir').'/migrations', \FilesystemIterator::SKIP_DOTS);

        if (iterator_count($iterator)) {
            $args = ['console', 'doctrine:migrations:migrate', '--no-interaction', '--env='.MILEX_ENV];

            if ('prod' === MILEX_ENV) {
                $args[] = '--no-debug';
            }

            $input       = new ArgvInput($args);
            $application = new Application($this->get('kernel'));
            $application->setAutoExit(false);
            $output = new BufferedOutput();

            $minExecutionTime = 300;
            $maxExecutionTime = (int) ini_get('max_execution_time');
            if ($maxExecutionTime > 0 && $maxExecutionTime < $minExecutionTime) {
                ini_set('max_execution_time', "$minExecutionTime");
            }

            $result = $application->run($input, $output);

            $outputBuffer = $output->fetch();

            // Check if migrations executed
            $noMigrations = (0 === $result && false !== strpos($outputBuffer, 'No migrations'));
        }

        if (0 !== $result) {
            // Log the output
            $outputBuffer = trim(preg_replace('/\n\s*\n/s', ' \\ ', $outputBuffer));
            $outputBuffer = preg_replace('/\s\s+/', ' ', trim($outputBuffer));
            $this->get('monolog.logger.milex')->log('error', '[UPGRADE ERROR] Exit code '.$result.'; '.$outputBuffer);

            $failed = true;
        } elseif ($this->request->get('update', 0)) {
            // This was a retry from the update so call up the finalizeAction to finish the process
            $this->forward('MilexCoreBundle:Ajax:updateFinalization',
                [
                    'request' => $this->request,
                ]
            );
        }

        return $this->delegateView([
            'viewParameters' => [
                'failed'       => $failed,
                'noMigrations' => $noMigrations,
            ],
            'contentTemplate' => 'MilexCoreBundle:Update:schema.html.php',
            'passthroughVars' => [
                'milexContent' => 'update',
                'route'         => $this->generateUrl('milex_core_update_schema'),
            ],
        ]);
    }
}
