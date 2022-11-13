<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Provider;

use Milex\LeadBundle\Exception\ChoicesNotFoundException;

interface FieldChoicesProviderInterface
{
    /**
     * @throws ChoicesNotFoundException
     *
     * @return mixed[]
     */
    public function getChoicesForField(string $fieldType, string $fieldAlias, string $search = ''): array;
}
