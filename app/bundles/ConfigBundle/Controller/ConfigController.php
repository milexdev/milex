<?php

namespace Milex\ConfigBundle\Controller;

use Milex\ConfigBundle\ConfigEvents;
use Milex\ConfigBundle\Event\ConfigBuilderEvent;
use Milex\ConfigBundle\Event\ConfigEvent;
use Milex\ConfigBundle\Form\Type\ConfigType;
use Milex\CoreBundle\Controller\FormController;
use Milex\CoreBundle\Helper\CacheHelper;
use Milex\CoreBundle\Helper\EncryptionHelper;
use Milex\CoreBundle\Helper\PathsHelper;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ConfigController extends FormController
{
    /**
     * Controller action for editing the application configuration.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction()
    {
        //admin only allowed
        if (!$this->user->isAdmin()) {
            return $this->accessDenied();
        }

        $event      = new ConfigBuilderEvent($this->get('milex.helper.bundle'));
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch(ConfigEvents::CONFIG_ON_GENERATE, $event);
        $fileFields  = $event->getFileFields();
        $formThemes  = $event->getFormThemes();
        $formConfigs = $this->get('milex.config.mapper')->bindFormConfigsWithRealValues($event->getForms());

        $this->mergeParamsWithLocal($formConfigs);

        // Create the form
        $action = $this->generateUrl('milex_config_action', ['objectAction' => 'edit']);
        $form   = $this->get('form.factory')->create(
            ConfigType::class,
            $formConfigs,
            [
                'action'     => $action,
                'fileFields' => $fileFields,
            ]
        );

        $originalNormData = $form->getNormData();

        /** @var \Milex\CoreBundle\Configurator\Configurator $configurator */
        $configurator = $this->get('milex.configurator');
        $isWritabale  = $configurator->isFileWritable();
        $openTab      = null;

        // Check for a submitted form and process it
        if ('POST' == $this->request->getMethod()) {
            if (!$cancelled = $this->isFormCancelled($form)) {
                $isValid = false;
                if ($isWritabale && $isValid = $this->isFormValid($form)) {
                    // Bind request to the form
                    $post     = $this->request->request;
                    $formData = $form->getData();

                    // Dispatch pre-save event. Bundles may need to modify some field values like passwords before save
                    $configEvent = new ConfigEvent($formData, $post);
                    $configEvent
                        ->setOriginalNormData($originalNormData)
                        ->setNormData($form->getNormData());
                    $dispatcher->dispatch(ConfigEvents::CONFIG_PRE_SAVE, $configEvent);
                    $formValues = $configEvent->getConfig();

                    $errors      = $configEvent->getErrors();
                    $fieldErrors = $configEvent->getFieldErrors();

                    if ($errors || $fieldErrors) {
                        foreach ($errors as $message => $messageVars) {
                            $form->addError(
                                new FormError($this->translator->trans($message, $messageVars, 'validators'))
                            );
                        }

                        foreach ($fieldErrors as $key => $fields) {
                            foreach ($fields as $field => $fieldError) {
                                $form[$key][$field]->addError(
                                    new FormError($this->translator->trans($fieldError[0], $fieldError[1], 'validators'))
                                );
                            }
                        }
                        $isValid = false;
                    } else {
                        // Prevent these from getting overwritten with empty values
                        $unsetIfEmpty = $configEvent->getPreservedFields();
                        $unsetIfEmpty = array_merge($unsetIfEmpty, $fileFields);

                        // Merge each bundle's updated configuration into the local configuration
                        foreach ($formValues as $object) {
                            $checkThese = array_intersect(array_keys($object), $unsetIfEmpty);
                            foreach ($checkThese as $checkMe) {
                                if (empty($object[$checkMe])) {
                                    unset($object[$checkMe]);
                                }
                            }

                            $configurator->mergeParameters($object);
                        }

                        try {
                            // Ensure the config has a secret key
                            $params = $configurator->getParameters();
                            if (empty($params['secret_key'])) {
                                $configurator->mergeParameters(['secret_key' => EncryptionHelper::generateKey()]);
                            }

                            $configurator->write();
                            $dispatcher->dispatch(ConfigEvents::CONFIG_POST_SAVE, $configEvent);

                            $this->addFlash('milex.config.config.notice.updated');

                            /** @var CacheHelper $cacheHelper */
                            $cacheHelper = $this->get('milex.helper.cache');
                            $cacheHelper->refreshConfig();

                            if ($isValid && !empty($formData['coreconfig']['last_shown_tab'])) {
                                $openTab = $formData['coreconfig']['last_shown_tab'];
                            }
                        } catch (\RuntimeException $exception) {
                            $this->addFlash('milex.config.config.error.not.updated', ['%exception%' => $exception->getMessage()], 'error');
                        }

                        $this->setLocale($params);
                    }
                } elseif (!$isWritabale) {
                    $form->addError(
                        new FormError(
                            $this->translator->trans('milex.config.notwritable')
                        )
                    );
                }
            }

            // If the form is saved or cancelled, redirect back to the dashboard
            if ($cancelled || $isValid) {
                if (!$cancelled && $this->isFormApplied($form)) {
                    $redirectParameters = ['objectAction' => 'edit'];
                    if ($openTab) {
                        $redirectParameters['tab'] = $openTab;
                    }

                    return $this->delegateRedirect($this->generateUrl('milex_config_action', $redirectParameters));
                } else {
                    return $this->delegateRedirect($this->generateUrl('milex_dashboard_index'));
                }
            }
        }

        $tmpl = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';

        return $this->delegateView(
            [
                'viewParameters' => [
                    'tmpl'        => $tmpl,
                    'security'    => $this->get('milex.security'),
                    'form'        => $this->setFormTheme($form, 'MilexConfigBundle:Config:form.html.php', $formThemes),
                    'formConfigs' => $formConfigs,
                    'isWritable'  => $isWritabale,
                ],
                'contentTemplate' => 'MilexConfigBundle:Config:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#milex_config_index',
                    'milexContent' => 'config',
                    'route'         => $this->generateUrl('milex_config_action', ['objectAction' => 'edit']),
                ],
            ]
        );
    }

    /**
     * @param $objectId
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function downloadAction($objectId)
    {
        //admin only allowed
        if (!$this->user->isAdmin()) {
            return $this->accessDenied();
        }

        $event      = new ConfigBuilderEvent($this->get('milex.helper.bundle'));
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch(ConfigEvents::CONFIG_ON_GENERATE, $event);

        // Extract and base64 encode file contents
        $fileFields = $event->getFileFields();

        if (!in_array($objectId, $fileFields)) {
            return $this->accessDenied();
        }

        $content  = $this->get('milex.helper.core_parameters')->get($objectId);
        $filename = $this->request->get('filename', $objectId);

        if ($decoded = base64_decode($content)) {
            $response = new Response($decoded);
            $response->headers->set('Content-Type', 'application/force-download');
            $response->headers->set('Content-Type', 'application/octet-stream');
            $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename);
            $response->headers->set('Expires', '0');
            $response->headers->set('Cache-Control', 'must-revalidate');
            $response->headers->set('Pragma', 'public');

            return $response;
        }

        return $this->notFound();
    }

    /**
     * @param $objectId
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function removeAction($objectId)
    {
        //admin only allowed
        if (!$this->user->isAdmin()) {
            return $this->accessDenied();
        }

        $success    = 0;
        $event      = new ConfigBuilderEvent($this->get('milex.helper.bundle'));
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch(ConfigEvents::CONFIG_ON_GENERATE, $event);

        // Extract and base64 encode file contents
        $fileFields = $event->getFileFields();

        if (in_array($objectId, $fileFields)) {
            $configurator = $this->get('milex.configurator');
            $configurator->mergeParameters([$objectId => null]);
            try {
                $configurator->write();

                /** @var CacheHelper $cacheHelper */
                $cacheHelper = $this->get('milex.helper.cache');
                $cacheHelper->refreshConfig();
                $success = 1;
            } catch (\Exception $exception) {
            }
        }

        return new JsonResponse(['success' => $success]);
    }

    /**
     * Merges default parameters from each subscribed bundle with the local (real) params.
     */
    private function mergeParamsWithLocal(array &$forms): void
    {
        $doNotChange = $this->getParameter('milex.security.restrictedConfigFields');
        /** @var PathsHelper $pathsHelper */
        $pathsHelper     = $this->get('milex.helper.paths');
        $localConfigFile = $pathsHelper->getLocalConfigurationFile();

        // Import the current local configuration, $parameters is defined in this file

        /** @var array $parameters */
        include $localConfigFile;

        $localParams = $parameters;

        foreach ($forms as &$form) {
            // Merge the bundle params with the local params
            foreach ($form['parameters'] as $key => $value) {
                if (in_array($key, $doNotChange)) {
                    unset($form['parameters'][$key]);
                } elseif (array_key_exists($key, $localParams)) {
                    $paramValue               = $localParams[$key];
                    $form['parameters'][$key] = $paramValue;
                }
            }
        }
    }

    /**
     * @param array<string, string> $params
     */
    private function setLocale(array $params): void
    {
        $me     = $this->get('security.token_storage')->getToken()->getUser();
        $locale = $me->getLocale();

        if (empty($locale)) {
            $locale = $params['locale'] ?? $this->get('milex.helper.core_parameters')->get('locale');
        }

        $this->get('session')->set('_locale', $locale);
    }
}
