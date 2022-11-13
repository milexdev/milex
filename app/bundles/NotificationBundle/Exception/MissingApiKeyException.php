<?php

namespace Milex\NotificationBundle\Exception;

class MissingApiKeyException extends \Exception
{
    protected $message = 'Missing Notification API Key';
}
