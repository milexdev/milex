<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Entity;

interface IdentifierFieldEntityInterface
{
    /**
     * @return string[]
     */
    public static function getDefaultIdentifierFields(): array;
}
