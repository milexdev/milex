<?php

namespace Milex\LeadBundle\Segment\Query\Filter;

use Milex\LeadBundle\Segment\ContactSegmentFilter;
use Milex\LeadBundle\Segment\Query\QueryBuilder;

interface FilterQueryBuilderInterface
{
    /**
     * @return QueryBuilder
     */
    public function applyQuery(QueryBuilder $queryBuilder, ContactSegmentFilter $filter);

    /**
     * @return string returns the service id in the DIC container
     */
    public static function getServiceId();
}
