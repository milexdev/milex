<?php

namespace Milex\PageBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UntrackableUrlsEvent.
 */
class UntrackableUrlsEvent extends Event
{
    /**
     * @var array
     */
    private $doNotTrack = [
        '{webview_url}',
        '{unsubscribe_url}',
        '{trackable=(.*?)}',
    ];

    /**
     * @var string
     */
    private $content;

    /**
     * TrackableEvent constructor.
     *
     * @param $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * set a URL or token to not convert to trackables.
     *
     * @param $url
     */
    public function addNonTrackable($url)
    {
        $this->doNotTrack[] = $url;
    }

    /**
     * Get array of non-trackables.
     *
     * @return array
     */
    public function getDoNotTrackList()
    {
        return $this->doNotTrack;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
