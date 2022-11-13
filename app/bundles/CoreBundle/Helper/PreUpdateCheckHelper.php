<?php

declare(strict_types=1);

namespace Milex\CoreBundle\Helper;

use Milex\CoreBundle\Helper\Update\PreUpdateChecks\AbstractPreUpdateCheck;

class PreUpdateCheckHelper
{
    /**
     * @var AbstractPreUpdateCheck[]
     */
    private array $checks = [];

    public function addCheck(AbstractPreUpdateCheck $check): void
    {
        $this->checks[] = $check;
    }

    /**
     * Get all registered pre-update checks.
     *
     * @return AbstractPreUpdateCheck[]
     */
    public function getChecks(): array
    {
        return $this->checks;
    }
}
