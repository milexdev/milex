<?php

namespace Milex\LeadBundle\Segment\Decorator;

use Milex\LeadBundle\Segment\ContactSegmentFilterCrate;
use Milex\LeadBundle\Segment\Query\Filter\ComplexRelationValueFilterQueryBuilder;

/**
 * Class CompanyDecorator.
 */
class CompanyDecorator extends BaseDecorator
{
    /**
     * @return string
     */
    public function getRelationJoinTable()
    {
        return MILEX_TABLE_PREFIX.'companies_leads';
    }

    /**
     * @return string
     */
    public function getRelationJoinTableField()
    {
        return 'company_id';
    }

    /**
     * @return string
     */
    public function getQueryType(ContactSegmentFilterCrate $contactSegmentFilterCrate)
    {
        return ComplexRelationValueFilterQueryBuilder::getServiceId();
    }
}
