<?php

namespace Milex\QueueBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use Milex\QueueBundle\Queue\QueueConsumerResults;

/**
 * Class QueueConsumerEvent.
 */
class QueueConsumerEvent extends CommonEvent
{
    /**
     * @var array
     */
    private $payload;

    /**
     * @var string
     */
    private $result;

    public function __construct($payload = [])
    {
        $this->payload = $payload;
        $this->result  = QueueConsumerResults::DO_NOT_ACKNOWLEDGE;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param string $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * Checks if the event is for specific transport.
     *
     * @param string $transport
     *
     * @return bool
     */
    public function checkTransport($transport)
    {
        return isset($this->payload['transport']) && $this->payload['transport'] === $transport;
    }
}
