<?php

namespace Milex\PluginBundle\Controller;

use Milex\CoreBundle\Controller\FormController;
use Milex\PluginBundle\Event\PluginIntegrationAuthRedirectEvent;
use Milex\PluginBundle\PluginEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class AuthController.
 */
class AuthController extends FormController
{
    /**
     * @param string $integration
     *
     * @return JsonResponse
     */
    public function authCallbackAction($integration)
    {
        $isAjax  = $this->request->isXmlHttpRequest();
        $session = $this->get('session');

        /** @var \Milex\PluginBundle\Helper\IntegrationHelper $integrationHelper */
        $integrationHelper = $this->factory->getHelper('integration');
        $integrationObject = $integrationHelper->getIntegrationObject($integration);

        //check to see if the service exists
        if (!$integrationObject) {
            $session->set('milex.integration.postauth.message', ['milex.integration.notfound', ['%name%' => $integration], 'error']);
            if ($isAjax) {
                return new JsonResponse(['url' => $this->generateUrl('milex_integration_auth_postauth', ['integration' => $integration])]);
            } else {
                return new RedirectResponse($this->generateUrl('milex_integration_auth_postauth', ['integration' => $integration]));
            }
        }

        try {
            $error = $integrationObject->authCallback();
        } catch (\InvalidArgumentException $e) {
            $session->set('milex.integration.postauth.message', [$e->getMessage(), [], 'error']);
            $redirectUrl = $this->generateUrl('milex_integration_auth_postauth', ['integration' => $integration]);
            if ($isAjax) {
                return new JsonResponse(['url' => $redirectUrl]);
            } else {
                return new RedirectResponse($redirectUrl);
            }
        }

        //check for error
        if ($error) {
            $type    = 'error';
            $message = 'milex.integration.error.oauthfail';
            $params  = ['%error%' => $error];
        } else {
            $type    = 'notice';
            $message = 'milex.integration.notice.oauthsuccess';
            $params  = [];
        }

        $session->set('milex.integration.postauth.message', [$message, $params, $type]);

        $identifier[$integration] = null;
        $socialCache              = [];
        $userData                 = $integrationObject->getUserData($identifier, $socialCache);

        $session->set('milex.integration.'.$integration.'.userdata', $userData);

        return new RedirectResponse($this->generateUrl('milex_integration_auth_postauth', ['integration' => $integration]));
    }

    /**
     * @param $integration
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function authStatusAction($integration)
    {
        $postAuthTemplate = 'MilexPluginBundle:Auth:postauth.html.php';

        $session     = $this->get('session');
        $postMessage = $session->get('milex.integration.postauth.message');
        $userData    = [];

        if (isset($integration)) {
            $userData = $session->get('milex.integration.'.$integration.'.userdata');
        }

        $message = $type = '';
        $alert   = 'success';
        if (!empty($postMessage)) {
            $message = $this->translator->trans($postMessage[0], $postMessage[1], 'flashes');
            $session->remove('milex.integration.postauth.message');
            $type = $postMessage[2];
            if ('error' == $type) {
                $alert = 'danger';
            }
        }

        return $this->render($postAuthTemplate, ['message' => $message, 'alert' => $alert, 'data' => $userData]);
    }

    /**
     * @param $integration
     *
     * @return RedirectResponse
     */
    public function authUserAction($integration)
    {
        /** @var \Milex\PluginBundle\Helper\IntegrationHelper $integrationHelper */
        $integrationHelper = $this->factory->getHelper('integration');
        $integrationObject = $integrationHelper->getIntegrationObject($integration);

        $settings['method']      = 'GET';
        $settings['integration'] = $integrationObject->getName();

        /** @var \Milex\PluginBundle\Integration\AbstractIntegration $integrationObject */
        $event = $this->dispatcher->dispatch(
            PluginEvents::PLUGIN_ON_INTEGRATION_AUTH_REDIRECT,
            new PluginIntegrationAuthRedirectEvent(
                $integrationObject,
                $integrationObject->getAuthLoginUrl()
            )
        );
        $oauthUrl = $event->getAuthUrl();

        return new RedirectResponse($oauthUrl);
    }
}
