<?php

namespace Milex\CoreBundle\DependencyInjection\Compiler;

use Milex\CoreBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class TranslationsPass.
 */
class TranslationsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('translator.default')) {
            return;
        }

        $translator = $container->findDefinition('translator.default');
        $translator->setClass(Translator::class)
            ->setPublic(true);

        if (null === $translator || MILEX_ENV === 'prod') {
            return;
        }

        // Disable cache for dev and test environments
        $translatorOptions              = $translator->getArgument(4);
        $translatorOptions['cache_dir'] = null;
        $translator->replaceArgument(4, $translatorOptions);
    }
}
