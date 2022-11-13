<?php

namespace Milex\LeadBundle\Segment;

use Milex\LeadBundle\Provider\FilterOperatorProviderInterface;

class ContactSegmentFilterOperator
{
    /**
     * @var FilterOperatorProviderInterface
     */
    private $filterOperatorProvider;

    public function __construct(
        FilterOperatorProviderInterface $filterOperatorProvider
    ) {
        $this->filterOperatorProvider = $filterOperatorProvider;
    }

    /**
     * @param string $operator
     *
     * @return string
     */
    public function fixOperator($operator)
    {
        $options = $this->filterOperatorProvider->getAllOperators();

        if (empty($options[$operator])) {
            return $operator;
        }

        $operatorDetails = $options[$operator];

        return $operatorDetails['expr'];
    }
}
