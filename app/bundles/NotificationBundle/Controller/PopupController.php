<?php

namespace Milex\NotificationBundle\Controller;

use Milex\CoreBundle\Controller\CommonController;
use Milex\PageBundle\Entity\Page;
use Milex\PageBundle\Event\PageDisplayEvent;
use Milex\PageBundle\PageEvents;

class PopupController extends CommonController
{
    public function indexAction()
    {
        /** @var \Milex\CoreBundle\Templating\Helper\AssetsHelper $assetsHelper */
        $assetsHelper = $this->container->get('templating.helper.assets');
        $assetsHelper->addStylesheet('/app/bundles/NotificationBundle/Assets/css/popup/popup.css');

        $response = $this->render(
            'MilexNotificationBundle:Popup:index.html.php',
            [
                'siteUrl' => $this->coreParametersHelper->get('site_url'),
            ]
        );

        $content = $response->getContent();

        $event = new PageDisplayEvent($content, new Page());
        $this->dispatcher->dispatch(PageEvents::PAGE_ON_DISPLAY, $event);
        $content = $event->getContent();

        return $response->setContent($content);
    }
}
