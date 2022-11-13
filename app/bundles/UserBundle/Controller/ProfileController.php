<?php

namespace Milex\UserBundle\Controller;

use Milex\CoreBundle\Controller\FormController;
use Milex\CoreBundle\Helper\LanguageHelper;
use Milex\UserBundle\Model\UserModel;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class ProfileController.
 */
class ProfileController extends FormController
{
    /**
     * Generate's account profile.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        //get current user
        $me    = $this->get('security.token_storage')->getToken()->getUser();
        /** @var UserModel */
        $model = $this->getModel('user');

        //set some permissions
        $permissions = [
            'apiAccess' => ($this->get('milex.helper.core_parameters')->get('api_enabled')) ?
                $this->get('milex.security')->isGranted('api:access:full')
                : 0,
            'editName'     => $this->get('milex.security')->isGranted('user:profile:editname'),
            'editUsername' => $this->get('milex.security')->isGranted('user:profile:editusername'),
            'editPosition' => $this->get('milex.security')->isGranted('user:profile:editposition'),
            'editEmail'    => $this->get('milex.security')->isGranted('user:profile:editemail'),
        ];

        $action = $this->generateUrl('milex_user_account');
        $form   = $model->createForm($me, $this->get('form.factory'), $action, ['in_profile' => true]);

        $overrides = [];

        //make sure this user has access to edit privileged fields
        foreach ($permissions as $permName => $hasAccess) {
            if ('apiAccess' == $permName) {
                continue;
            }

            if (!$hasAccess) {
                //set the value to its original
                switch ($permName) {
                    case 'editName':
                        $overrides['firstName'] = $me->getFirstName();
                        $overrides['lastName']  = $me->getLastName();
                        $form->remove('firstName');
                        $form->add(
                            'firstName_unbound',
                            TextType::class,
                            [
                                'label'      => 'milex.core.firstname',
                                'label_attr' => ['class' => 'control-label'],
                                'attr'       => ['class' => 'form-control'],
                                'mapped'     => false,
                                'disabled'   => true,
                                'data'       => $me->getFirstName(),
                                'required'   => false,
                            ]
                        );

                        $form->remove('lastName');
                        $form->add(
                            'lastName_unbound',
                            TextType::class,
                            [
                                'label'      => 'milex.core.lastname',
                                'label_attr' => ['class' => 'control-label'],
                                'attr'       => ['class' => 'form-control'],
                                'mapped'     => false,
                                'disabled'   => true,
                                'data'       => $me->getLastName(),
                                'required'   => false,
                            ]
                        );
                        break;

                    case 'editUsername':
                        $overrides['username'] = $me->getUsername();
                        $form->remove('username');
                        $form->add(
                            'username_unbound',
                            TextType::class,
                            [
                                'label'      => 'milex.core.username',
                                'label_attr' => ['class' => 'control-label'],
                                'attr'       => ['class' => 'form-control'],
                                'mapped'     => false,
                                'disabled'   => true,
                                'data'       => $me->getUsername(),
                                'required'   => false,
                            ]
                        );
                        break;
                    case 'editPosition':
                        $overrides['position'] = $me->getPosition();
                        $form->remove('position');
                        $form->add(
                            'position_unbound',
                            TextType::class,
                            [
                                'label'      => 'milex.core.position',
                                'label_attr' => ['class' => 'control-label'],
                                'attr'       => ['class' => 'form-control'],
                                'mapped'     => false,
                                'disabled'   => true,
                                'data'       => $me->getPosition(),
                                'required'   => false,
                            ]
                        );
                        break;
                    case 'editEmail':
                        $overrides['email'] = $me->getEmail();
                        $form->remove('email');
                        $form->add(
                            'email_unbound',
                            TextType::class,
                            [
                                'label'      => 'milex.core.type.email',
                                'label_attr' => ['class' => 'control-label'],
                                'attr'       => ['class' => 'form-control'],
                                'mapped'     => false,
                                'disabled'   => true,
                                'data'       => $me->getEmail(),
                                'required'   => false,
                            ]
                        );
                        break;
                }
            }
        }

        //Check for a submitted form and process it
        $submitted = $this->get('session')->get('formProcessed', 0);
        if ('POST' == $this->request->getMethod() && !$submitted) {
            $this->get('session')->set('formProcessed', 1);

            //check to see if the password needs to be rehashed
            $formUser              = $this->request->request->get('user', []);
            $submittedPassword     = $formUser['plainPassword']['password'] ?? null;
            $encoder               = $this->get('security.password_encoder');
            $overrides['password'] = $model->checkNewPassword($me, $encoder, $submittedPassword);
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($this->isFormValid($form)) {
                    foreach ($overrides as $k => $v) {
                        $func = 'set'.ucfirst($k);
                        $me->$func($v);
                    }

                    //form is valid so process the data
                    $model->saveEntity($me);

                    //check if the user's locale has been downloaded already, fetch it if not
                    /** @var LanguageHelper $languageHelper */
                    $languageHelper     = $this->container->get('milex.helper.language');
                    $installedLanguages = $languageHelper->getSupportedLanguages();

                    if ($me->getLocale() && !array_key_exists($me->getLocale(), $installedLanguages)) {
                        $fetchLanguage = $languageHelper->extractLanguagePackage($me->getLocale());

                        // If there is an error, we need to reset the user's locale to the default
                        if ($fetchLanguage['error']) {
                            $me->setLocale(null);
                            $model->saveEntity($me);
                            $message     = 'milex.core.could.not.set.language';
                            $messageVars = [];

                            if (isset($fetchLanguage['message'])) {
                                $message = $fetchLanguage['message'];
                            }

                            if (isset($fetchLanguage['vars'])) {
                                $messageVars = $fetchLanguage['vars'];
                            }

                            $this->addFlash($message, $messageVars);
                        }
                    }

                    // Update timezone and locale
                    $tz = $me->getTimezone();
                    if (empty($tz)) {
                        $tz = $this->get('milex.helper.core_parameters')->get('default_timezone');
                    }
                    $this->get('session')->set('_timezone', $tz);

                    $locale = $me->getLocale();
                    if (empty($locale)) {
                        $locale = $this->get('milex.helper.core_parameters')->get('locale');
                    }
                    $this->get('session')->set('_locale', $locale);

                    $returnUrl = $this->generateUrl('milex_user_account');

                    return $this->postActionRedirect(
                        [
                            'returnUrl'       => $returnUrl,
                            'contentTemplate' => 'MilexUserBundle:Profile:index',
                            'passthroughVars' => [
                                'milexContent' => 'user',
                            ],
                            'flashes' => [ //success
                                [
                                    'type' => 'notice',
                                    'msg'  => 'milex.user.account.notice.updated',
                                ],
                            ],
                        ]
                    );
                }
            } else {
                return $this->redirect($this->generateUrl('milex_dashboard_index'));
            }
        }
        $this->get('session')->set('formProcessed', 0);

        $parameters = [
            'permissions'       => $permissions,
            'me'                => $me,
            'userForm'          => $form->createView(),
            'authorizedClients' => $this->forward('MilexApiBundle:Client:authorizedClients')->getContent(),
        ];

        return $this->delegateView(
            [
                'viewParameters'  => $parameters,
                'contentTemplate' => 'MilexUserBundle:Profile:index.html.twig',
                'passthroughVars' => [
                    'route'         => $this->generateUrl('milex_user_account'),
                    'milexContent' => 'user',
                ],
            ]
        );
    }
}
