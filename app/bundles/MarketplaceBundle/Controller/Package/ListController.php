<?php

declare(strict_types=1);

namespace Milex\MarketplaceBundle\Controller\Package;

use Milex\CoreBundle\Controller\CommonController;
use Milex\CoreBundle\Helper\InputHelper;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\MarketplaceBundle\Security\Permissions\MarketplacePermissions;
use Milex\MarketplaceBundle\Service\Config;
use Milex\MarketplaceBundle\Service\PluginCollector;
use Milex\MarketplaceBundle\Service\RouteProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class ListController extends CommonController
{
    private PluginCollector $pluginCollector;

    private RequestStack $requestStack;

    private RouteProvider $routeProvider;

    private CorePermissions $corePermissions;

    private Config $config;

    public function __construct(
        PluginCollector $pluginCollector,
        RequestStack $requestStack,
        RouteProvider $routeProvider,
        CorePermissions $corePermissions,
        Config $config
    ) {
        $this->pluginCollector = $pluginCollector;
        $this->requestStack    = $requestStack;
        $this->routeProvider   = $routeProvider;
        $this->corePermissions = $corePermissions;
        $this->config          = $config;
    }

    public function listAction(int $page = 1): Response
    {
        if (!$this->config->marketplaceIsEnabled()) {
            return $this->notFound();
        }

        if (!$this->corePermissions->isGranted(MarketplacePermissions::CAN_VIEW_PACKAGES)) {
            return $this->accessDenied();
        }

        $request = $this->requestStack->getCurrentRequest();
        $search  = InputHelper::clean($request->get('search', ''));
        $limit   = (int) $request->get('limit', 30);
        $route   = $this->routeProvider->buildListRoute($page);

        return $this->delegateView(
            [
                'returnUrl'      => $route,
                'viewParameters' => [
                    'searchValue'       => $search,
                    'items'             => $this->pluginCollector->collectPackages($page, $limit, $search),
                    'count'             => $this->pluginCollector->getTotal(),
                    'page'              => $page,
                    'limit'             => $limit,
                    'tmpl'              => $request->isXmlHttpRequest() ? $request->get('tmpl', 'index') : 'index',
                    'isComposerEnabled' => $this->config->isComposerEnabled(),
                ],
                'contentTemplate' => 'MarketplaceBundle:Package:list.html.php',
                'passthroughVars' => [
                    'milexContent' => 'package',
                    'route'         => $route,
                ],
            ]
        );
    }
}
