<?php

namespace Milex\CoreBundle\Factory;

use Milex\CoreBundle\Helper\CoreParametersHelper;
use Milex\CoreBundle\Helper\PageHelper;
use Milex\CoreBundle\Helper\PageHelperInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class PageHelperFactory implements PageHelperFactoryInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    public function __construct(SessionInterface $session, CoreParametersHelper $coreParametersHelper)
    {
        $this->session              = $session;
        $this->coreParametersHelper = $coreParametersHelper;
    }

    public function make(string $sessionPrefix, int $page): PageHelperInterface
    {
        return new PageHelper($this->session, $this->coreParametersHelper, $sessionPrefix, $page);
    }
}
