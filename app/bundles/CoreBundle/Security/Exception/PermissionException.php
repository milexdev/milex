<?php

namespace Milex\CoreBundle\Security\Exception;

class PermissionException extends \InvalidArgumentException
{
    protected $code = 403;
}
