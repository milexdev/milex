<?php

declare(strict_types=1);

namespace Milex\MarketplaceBundle\Controller\Package;

use Milex\CoreBundle\Controller\CommonController;
use Milex\CoreBundle\Helper\ComposerHelper;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\MarketplaceBundle\Exception\RecordNotFoundException;
use Milex\MarketplaceBundle\Model\PackageModel;
use Milex\MarketplaceBundle\Security\Permissions\MarketplacePermissions;
use Milex\MarketplaceBundle\Service\Config;
use Milex\MarketplaceBundle\Service\RouteProvider;
use Symfony\Component\HttpFoundation\Response;

class DetailController extends CommonController
{
    private PackageModel $packageModel;
    private RouteProvider $routeProvider;
    private CorePermissions $corePermissions;
    private Config $config;
    private ComposerHelper $composer;

    public function __construct(
        PackageModel $packageModel,
        RouteProvider $routeProvider,
        CorePermissions $corePermissions,
        Config $config,
        ComposerHelper $composer
    ) {
        $this->packageModel    = $packageModel;
        $this->routeProvider   = $routeProvider;
        $this->corePermissions = $corePermissions;
        $this->config          = $config;
        $this->composer        = $composer;
    }

    public function ViewAction(string $vendor, string $package): Response
    {
        if (!$this->config->marketplaceIsEnabled()) {
            return $this->notFound();
        }

        if (!$this->corePermissions->isGranted(MarketplacePermissions::CAN_VIEW_PACKAGES)) {
            return $this->accessDenied();
        }

        $isInstalled = $this->composer->isInstalled("{$vendor}/{$package}");

        try {
            $packageDetail = $this->packageModel->getPackageDetail("{$vendor}/{$package}");
        } catch (RecordNotFoundException $e) {
            return $this->notFound($e->getMessage());
        }

        return $this->delegateView(
            [
                'returnUrl'      => $this->routeProvider->buildListRoute(),
                'viewParameters' => [
                    'packageDetail'     => $packageDetail,
                    'isInstalled'       => $isInstalled,
                    'isComposerEnabled' => $this->config->isComposerEnabled(),
                ],
                'contentTemplate' => 'MarketplaceBundle:Package:detail.html.php',
                'passthroughVars' => [
                    'milexContent' => 'package',
                    'activeLink'    => '#milex_marketplace',
                    'route'         => $this->routeProvider->buildDetailRoute($vendor, $package),
                ],
            ]
        );
    }
}
