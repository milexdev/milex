<?php

namespace Milex\CoreBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\MatcherInterface;
use Knp\Menu\Renderer\RendererInterface;
use Milex\CoreBundle\Helper\TemplatingHelper;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;

class MenuRenderer implements RendererInterface
{
    /**
     * @var DelegatingEngine
     */
    private $engine;

    /**
     * @var MatcherInterface
     */
    private $matcher;

    /**
     * @var array
     */
    private $defaultOptions;

    public function __construct(MatcherInterface $matcher, TemplatingHelper $templatingHelper, array $defaultOptions = [])
    {
        $this->engine         = $templatingHelper->getTemplating();
        $this->matcher        = $matcher;
        $this->defaultOptions = array_merge(
            [
                'depth'             => null,
                'matchingDepth'     => null,
                'currentAsLink'     => true,
                'currentClass'      => 'active',
                'ancestorClass'     => 'open',
                'firstClass'        => 'first',
                'lastClass'         => 'last',
                'template'          => 'MilexCoreBundle:Menu:main.html.php',
                'compressed'        => false,
                'allow_safe_labels' => false,
                'clear_matcher'     => true,
            ],
            $defaultOptions
        );
    }

    /**
     * Renders menu.
     */
    public function render(ItemInterface $item, array $options = []): string
    {
        $options = array_merge($this->defaultOptions, $options);

        if ($options['clear_matcher']) {
            $this->matcher->clear();
        }

        //render html
        $html = $this->engine->render($options['template'], [
            'item'    => $item,
            'options' => $options,
            'matcher' => $this->matcher,
        ]);

        return $html;
    }
}