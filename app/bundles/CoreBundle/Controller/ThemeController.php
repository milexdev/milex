<?php

namespace Milex\CoreBundle\Controller;

use Milex\CoreBundle\Form\Type\ThemeUploadType;
use Milex\CoreBundle\Helper\InputHelper;
use Milex\CoreBundle\Helper\ThemeHelperInterface;
use Milex\IntegrationsBundle\Helper\BuilderIntegrationsHelper;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ThemeController.
 */
class ThemeController extends FormController
{
    /**
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        //set some permissions
        $permissions = $this->get('milex.security')->isGranted([
            'core:themes:view',
            'core:themes:create',
            'core:themes:edit',
            'core:themes:delete',
        ], 'RETURN_ARRAY');

        if (!$permissions['core:themes:view']) {
            return $this->accessDenied();
        }

        /** @var ThemeHelperInterface $themeHelper */
        $themeHelper = $this->container->get('milex.helper.theme');
        /** @var BuilderIntegrationsHelper $builderIntegrationsHelper */
        $builderIntegrationsHelper    = $this->container->get('milex.integrations.helper.builder_integrations');

        $dir         = $this->factory->getSystemPath('themes', true);
        $action      = $this->generateUrl('milex_themes_index');
        $form        = $this->get('form.factory')->create(ThemeUploadType::class, [], ['action' => $action]);

        if ('POST' == $this->request->getMethod()) {
            if (isset($form) && !$cancelled = $this->isFormCancelled($form)) {
                if ($this->isFormValid($form)) {
                    $fileData = $form['file']->getData();

                    if (!$fileData) {
                        $form->addError(
                            new FormError(
                                $this->translator->trans('milex.core.theme.upload.empty', [], 'validators')
                            )
                        );
                    } else {
                        $fileName  = InputHelper::filename($fileData->getClientOriginalName());
                        $themeName = basename($fileName, '.zip');

                        if (!empty($fileData)) {
                            $extension = pathinfo($fileName, PATHINFO_EXTENSION);

                            if ('zip' === $extension) {
                                try {
                                    $fileData->move($dir, $fileName);
                                    $themeHelper->install($dir.'/'.$fileName);
                                    $this->addFlash('milex.core.theme.installed', ['%name%' => $themeName]);
                                } catch (\Exception $e) {
                                    $form->addError(
                                        new FormError(
                                            $this->translator->trans($e->getMessage(), [], 'validators')
                                        )
                                    );
                                }
                            } else {
                                $form->addError(
                                    new FormError(
                                        $this->translator->trans('milex.core.not.allowed.file.extension', ['%extension%' => $extension], 'validators')
                                    )
                                );
                            }
                        } else {
                            $form->addError(
                                new FormError(
                                    $this->translator->trans('milex.dashboard.upload.filenotfound', [], 'validators')
                                )
                            );
                        }
                    }
                }
            }
        }

        return $this->delegateView([
            'viewParameters' => [
                'items'         => $themeHelper->getInstalledThemes('all', true, true),
                'builders'      => $builderIntegrationsHelper->getBuilderNames(),
                'defaultThemes' => $themeHelper->getDefaultThemes(),
                'form'          => $form->createView(),
                'permissions'   => $permissions,
                'security'      => $this->get('milex.security'),
            ],
            'contentTemplate' => 'MilexCoreBundle:Theme:list.html.php',
            'passthroughVars' => [
                'activeLink'    => '#milex_themes_index',
                'milexContent' => 'theme',
                'route'         => $this->generateUrl('milex_themes_index'),
            ],
        ]);
    }

    /**
     * Download a theme.
     *
     * @param string $themeName
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function downloadAction($themeName)
    {
        /** @var ThemeHelperInterface $themeHelper */
        $themeHelper = $this->container->get('milex.helper.theme');
        $flashes     = [];
        $error       = false;

        if (!$this->get('milex.security')->isGranted('core:themes:view')) {
            return $this->accessDenied();
        }

        if (!$themeHelper->exists($themeName)) {
            $flashes[] = [
                'type'    => 'error',
                'msg'     => 'milex.core.theme.error.notfound',
                'msgVars' => ['%theme%' => $themeName],
            ];
            $error = true;
        }

        try {
            $zipPath = $themeHelper->zip($themeName);
        } catch (\Exception $e) {
            $flashes[] = [
                'type' => 'error',
                'msg'  => $e->getMessage(),
            ];
            $error = true;
        }

        if (!$error && !$zipPath) {
            $flashes[] = [
                'type' => 'error',
                'msg'  => 'milex.core.permission.issue',
            ];
            $error = true;
        }

        if ($error) {
            return $this->postActionRedirect(
                array_merge($this->getIndexPostActionVars(), [
                    'flashes' => $flashes,
                ])
            );
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Length', (string) filesize($zipPath));

        $stream = $this->request->get('stream', 0);

        if (!$stream) {
            $response->headers->set('Content-Disposition', 'attachment;filename="'.$themeName.'.zip"');
        }

        $response->setContent(file_get_contents($zipPath));

        return $response;
    }

    /**
     * Deletes the theme.
     *
     * @param string $themeName
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($themeName)
    {
        $flashes = [];

        if ('POST' == $this->request->getMethod()) {
            $flashes = $this->deleteTheme($themeName);
        }

        return $this->postActionRedirect(
            array_merge($this->getIndexPostActionVars(), [
                'flashes' => $flashes,
            ])
        );
    }

    /**
     * Deletes a group of themes.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        $flashes = [];

        if ('POST' == $this->request->getMethod()) {
            $themeNames = json_decode($this->request->query->get('ids', '{}'));

            foreach ($themeNames as $themeName) {
                $flashes = $this->deleteTheme($themeName);
            }
        }

        return $this->postActionRedirect(
            array_merge($this->getIndexPostActionVars(), [
                'flashes' => $flashes,
            ])
        );
    }

    /**
     * Deletes a theme.
     *
     * @return array
     */
    public function deleteTheme($themeName)
    {
        $flashes     = [];
        $themeHelper = $this->container->get('milex.helper.theme');

        if (!$themeHelper->exists($themeName)) {
            $flashes[] = [
                'type'    => 'error',
                'msg'     => 'milex.core.theme.error.notfound',
                'msgVars' => ['%theme%' => $themeName],
            ];
        } elseif (!$this->get('milex.security')->isGranted('core:themes:delete')) {
            return $this->accessDenied();
        } elseif (in_array($themeName, $themeHelper->getDefaultThemes())) {
            $flashes[] = [
                'type'    => 'error',
                'msg'     => 'milex.core.theme.cannot.be.removed',
                'msgVars' => ['%theme%' => $themeName],
            ];
        } else {
            try {
                $theme = $themeHelper->getTheme($themeName);
                $themeHelper->delete($themeName);
            } catch (\Exception $e) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'milex.core.error.delete.error',
                    'msgVars' => ['%error%' => $e->getMessage()],
                ];
            }

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'milex.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $theme->getName(),
                    '%id%'   => $themeName,
                ],
            ];
        }

        return $flashes;
    }

    /**
     * A helper method to keep the code DRY.
     *
     * @return array
     */
    public function getIndexPostActionVars()
    {
        return [
            'returnUrl'       => $this->generateUrl('milex_themes_index'),
            'contentTemplate' => 'MilexCoreBundle:theme:index',
            'passthroughVars' => [
                'activeLink'    => 'milex_themes_index',
                'milexContent' => 'theme',
            ],
        ];
    }
}
