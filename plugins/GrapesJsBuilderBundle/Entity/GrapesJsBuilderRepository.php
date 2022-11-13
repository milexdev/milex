<?php

declare(strict_types=1);

namespace MilexPlugin\GrapesJsBuilderBundle\Entity;

use Milex\CoreBundle\Entity\CommonRepository;

class GrapesJsBuilderRepository extends CommonRepository
{
    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 'gjb';
    }
}
