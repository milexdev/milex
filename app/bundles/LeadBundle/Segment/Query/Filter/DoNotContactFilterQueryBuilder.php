<?php

namespace Milex\LeadBundle\Segment\Query\Filter;

use Milex\LeadBundle\Segment\ContactSegmentFilter;
use Milex\LeadBundle\Segment\Query\QueryBuilder;
use Milex\LeadBundle\Segment\Query\QueryException;

class DoNotContactFilterQueryBuilder extends BaseFilterQueryBuilder
{
    public static function getServiceId(): string
    {
        return 'milex.lead.query.builder.special.dnc';
    }

    /**
     * @throws QueryException
     */
    public function applyQuery(QueryBuilder $queryBuilder, ContactSegmentFilter $filter): QueryBuilder
    {
        $leadsTableAlias   = $queryBuilder->getTableAlias(MILEX_TABLE_PREFIX.'leads');
        $doNotContactParts = $filter->getDoNotContactParts();
        $expr              = $queryBuilder->expr();
        $queryAlias        = $this->generateRandomParameterName();
        $reasonParameter   = ":{$queryAlias}reason";
        $channelParameter  = ":{$queryAlias}channel";

        $queryBuilder->setParameter($reasonParameter, $doNotContactParts->getParameterType());
        $queryBuilder->setParameter($channelParameter, $doNotContactParts->getChannel());

        $filterQueryBuilder = $queryBuilder->createQueryBuilder()
            ->select($queryAlias.'.lead_id')
            ->from(MILEX_TABLE_PREFIX.'lead_donotcontact', $queryAlias)
            ->andWhere($expr->eq($queryAlias.'.reason', $reasonParameter))
            ->andWhere($expr->eq($queryAlias.'.channel', $channelParameter));

        if ('eq' === $filter->getOperator() xor !$filter->getParameterValue()) {
            $expression = $expr->in($leadsTableAlias.'.id', $filterQueryBuilder->getSQL());
        } else {
            $expression = $expr->notIn($leadsTableAlias.'.id', $filterQueryBuilder->getSQL());
        }

        $queryBuilder->addLogic($expression, $filter->getGlue());

        return $queryBuilder;
    }
}
