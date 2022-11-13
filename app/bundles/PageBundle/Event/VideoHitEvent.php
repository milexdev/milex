<?php

namespace Milex\PageBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use Milex\PageBundle\Entity\VideoHit;

class VideoHitEvent extends CommonEvent
{
    protected $request;

    protected $code;

    /**
     * @param $request
     * @param $code
     */
    public function __construct(VideoHit $hit, $request, $code)
    {
        $this->entity  = $hit;
        $this->request = $request;
        $this->code    = $code;
    }

    /**
     * Get page request.
     *
     * @return string
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get HTML code.
     *
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return VideoHit
     */
    public function getHit()
    {
        return $this->entity;
    }
}
