<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Field\Event;

use Milex\LeadBundle\Entity\LeadField;
use Symfony\Component\EventDispatcher\Event;

final class AddColumnBackgroundEvent extends Event
{
    /**
     * @var LeadField
     */
    private $leadField;

    public function __construct(LeadField $leadField)
    {
        $this->leadField = $leadField;
    }

    public function getLeadField(): LeadField
    {
        return $this->leadField;
    }
}
