<?php

declare(strict_types=1);

namespace Milex\MarketplaceBundle\Controller;

use Milex\CoreBundle\Controller\CommonController;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\MarketplaceBundle\Security\Permissions\MarketplacePermissions;
use Milex\MarketplaceBundle\Service\Allowlist;
use Milex\MarketplaceBundle\Service\Config;
use Symfony\Component\HttpFoundation\Response;

class CacheController extends CommonController
{
    private CorePermissions $corePermissions;
    private Config $config;
    private Allowlist $allowlist;

    public function __construct(
        CorePermissions $corePermissions,
        Config $config,
        Allowlist $allowlist
    ) {
        $this->corePermissions = $corePermissions;
        $this->config          = $config;
        $this->allowlist       = $allowlist;
    }

    public function ClearAction(): Response
    {
        if (!$this->config->marketplaceIsEnabled()) {
            return $this->notFound();
        }

        if (!$this->corePermissions->isGranted(MarketplacePermissions::CAN_VIEW_PACKAGES)) {
            return $this->accessDenied();
        }

        $this->allowlist->clearCache();

        return $this->forward(
            'MarketplaceBundle:Package\List:list'
        );
    }
}
