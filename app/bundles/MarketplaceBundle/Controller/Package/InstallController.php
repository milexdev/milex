<?php

declare(strict_types=1);

namespace Milex\MarketplaceBundle\Controller\Package;

use Milex\CoreBundle\Controller\CommonController;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\MarketplaceBundle\Model\PackageModel;
use Milex\MarketplaceBundle\Security\Permissions\MarketplacePermissions;
use Milex\MarketplaceBundle\Service\Config;
use Milex\MarketplaceBundle\Service\RouteProvider;
use Symfony\Component\HttpFoundation\Response;

class InstallController extends CommonController
{
    private PackageModel $packageModel;

    private RouteProvider $routeProvider;

    private CorePermissions $corePermissions;

    private Config $config;

    public function __construct(
        PackageModel $packageModel,
        RouteProvider $routeProvider,
        CorePermissions $corePermissions,
        Config $config
    ) {
        $this->packageModel    = $packageModel;
        $this->routeProvider   = $routeProvider;
        $this->corePermissions = $corePermissions;
        $this->config          = $config;
    }

    public function viewAction(string $vendor, string $package): Response
    {
        if (!$this->config->marketplaceIsEnabled()) {
            return $this->notFound();
        }

        if (!$this->corePermissions->isGranted(MarketplacePermissions::CAN_INSTALL_PACKAGES)
            || !$this->config->isComposerEnabled()) {
            return $this->accessDenied();
        }

        return $this->delegateView(
            [
                'returnUrl'      => $this->routeProvider->buildListRoute(),
                'viewParameters' => [
                    'packageDetail'  => $this->packageModel->getPackageDetail("{$vendor}/{$package}"),
                ],
                'contentTemplate' => 'MarketplaceBundle:Package:install.html.php',
                'passthroughVars' => [
                    'milexContent' => 'package',
                    'activeLink'    => '#milex_marketplace',
                    'route'         => $this->routeProvider->buildInstallRoute($vendor, $package),
                ],
            ]
        );
    }
}
