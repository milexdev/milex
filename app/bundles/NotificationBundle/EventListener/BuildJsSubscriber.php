<?php

namespace Milex\NotificationBundle\EventListener;

use Milex\CoreBundle\CoreEvents;
use Milex\CoreBundle\Event\BuildJsEvent;
use Milex\NotificationBundle\Helper\NotificationHelper;
use Milex\PluginBundle\Helper\IntegrationHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class BuildJsSubscriber implements EventSubscriberInterface
{
    /**
     * @var NotificationHelper
     */
    private $notificationHelper;

    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(NotificationHelper $notificationHelper, IntegrationHelper $integrationHelper, RouterInterface $router)
    {
        $this->notificationHelper = $notificationHelper;
        $this->integrationHelper  = $integrationHelper;
        $this->router             = $router;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            CoreEvents::BUILD_MILEX_JS => ['onBuildJs', 254],
        ];
    }

    public function onBuildJs(BuildJsEvent $event)
    {
        $integration = $this->integrationHelper->getIntegrationObject('OneSignal');

        if (!$integration || false === $integration->getIntegrationSettings()->getIsPublished()) {
            return;
        }

        $subscribeUrl   = $this->router->generate('milex_notification_popup', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $subscribeTitle = 'Subscribe To Notifications';
        $width          = 450;
        $height         = 450;

        $js = <<<JS
        
        {$this->notificationHelper->getHeaderScript()}
       
MilexJS.notification = {
    init: function () {
        
        {$this->notificationHelper->getScript()}
         
        var subscribeButton = document.getElementById('milex-notification-subscribe');

        if (subscribeButton) {
            subscribeButton.addEventListener('click', MilexJS.notification.popup);
        }
    },

    popup: function () {
        var subscribeUrl = '{$subscribeUrl}';
        var subscribeTitle = '{$subscribeTitle}';
        var w = {$width};
        var h = {$height};

        // Fixes dual-screen position                         Most browsers      Firefox
        var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
        var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

        var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
        var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

        var left = ((width / 2) - (w / 2)) + dualScreenLeft;
        var top = ((height / 2) - (h / 2)) + dualScreenTop;

        var subscribeWindow = window.open(
            subscribeUrl,
            subscribeTitle,
            'scrollbars=yes, width=' + w + ',height=' + h + ',top=' + top + ',left=' + left + ',directories=0,titlebar=0,toolbar=0,location=0,status=0,menubar=0,scrollbars=no,resizable=no'
        );

        if (window.focus) {
            subscribeWindow.focus();
        }
        
        window.closeSubscribeWindow = function() { subscribeWindow.close(); };
    }
};

MilexJS.documentReady(MilexJS.notification.init);
JS;

        $event->appendJs($js, 'Milex Notification JS');
    }
}