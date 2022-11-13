<?php

namespace Milex\DynamicContentBundle\Entity;

use Milex\CoreBundle\Entity\CommonRepository;

/**
 * Class DownloadRepository.
 */
class DynamicContentLeadDataRepository extends CommonRepository
{
    /**
     * @return string
     */
    public function getTableAlias()
    {
        return 'dcld';
    }
}
