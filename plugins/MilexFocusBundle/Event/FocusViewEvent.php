<?php

namespace MilexPlugin\MilexFocusBundle\Event;

use MilexPlugin\MilexFocusBundle\Entity\Stat;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class FocusViewEvent.
 */
class FocusViewEvent extends Event
{
    /**
     * @var Stat
     */
    private $stat;

    public function __construct(Stat $stat)
    {
        $this->stat  = $stat;
    }

    /**
     * @return Stat
     */
    public function getStat()
    {
        return $this->stat;
    }
}
