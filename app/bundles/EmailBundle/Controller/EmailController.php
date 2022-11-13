<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Milex\EmailBundle\Controller;

use Milex\CoreBundle\Controller\BuilderControllerTrait;
use Milex\CoreBundle\Controller\FormController;
use Milex\CoreBundle\Controller\FormErrorMessagesTrait;
use Milex\CoreBundle\Event\DetermineWinnerEvent;
use Milex\CoreBundle\Form\Type\BuilderSectionType;
use Milex\CoreBundle\Form\Type\DateRangeType;
use Milex\CoreBundle\Helper\EmojiHelper;
use Milex\CoreBundle\Helper\InputHelper;
use Milex\EmailBundle\Entity\Email;
use Milex\EmailBundle\Form\Type\BatchSendType;
use Milex\EmailBundle\Form\Type\ExampleSendType;
use Milex\LeadBundle\Controller\EntityContactsTrait;
use Milex\LeadBundle\Model\ListModel;
use MilexPlugin\MilexCitrixBundle\Helper\CitrixHelper;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailController extends FormController
{
    use BuilderControllerTrait;
    use FormErrorMessagesTrait;
    use EntityContactsTrait;
    const EXAMPLE_EMAIL_SUBJECT_PREFIX = '[TEST]';

    /**
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page = 1)
    {
        $model = $this->getModel('email');

        //set some permissions
        $permissions = $this->get('milex.security')->isGranted(
            [
                'email:emails:viewown',
                'email:emails:viewother',
                'email:emails:create',
                'email:emails:editown',
                'email:emails:editother',
                'email:emails:deleteown',
                'email:emails:deleteother',
                'email:emails:publishown',
                'email:emails:publishother',
            ],
            'RETURN_ARRAY'
        );

        if (!$permissions['email:emails:viewown'] && !$permissions['email:emails:viewother']) {
            return $this->accessDenied();
        }

        $this->setListFilters();

        $session = $this->get('session');

        $listFilters = [
            'filters' => [
                'placeholder' => $this->get('translator')->trans('milex.email.filter.placeholder'),
                'multiple'    => true,
            ],
        ];

        // Reset available groups
        $listFilters['filters']['groups'] = [];

        //set limits
        $limit = $session->get('milex.email.limit', $this->coreParametersHelper->get('default_pagelimit'));
        $start = (1 === $page) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $search = $this->request->get('search', $session->get('milex.email.filter', ''));
        $session->set('milex.email.filter', $search);

        $filter = [
            'string' => $search,
            'force'  => [
                ['column' => 'e.variantParent', 'expr' => 'isNull'],
                ['column' => 'e.translationParent', 'expr' => 'isNull'],
            ],
        ];
        if (!$permissions['email:emails:viewother']) {
            $filter['force'][] =
                ['column' => 'e.createdBy', 'expr' => 'eq', 'value' => $this->user->getId()];
        }

        //retrieve a list of Lead Lists
        $listFilters['filters']['groups']['milex.core.filter.lists'] = [
            'options' => $this->getModel('lead.list')->getUserLists(),
            'prefix'  => 'list',
        ];

        //retrieve a list of themes
        $listFilters['filters']['groups']['milex.core.filter.themes'] = [
            'options' => $this->factory->getInstalledThemes('email'),
            'prefix'  => 'theme',
        ];

        $currentFilters = $session->get('milex.email.list_filters', []);
        $updatedFilters = $this->request->get('filters', false);
        $ignoreListJoin = true;

        if ($updatedFilters) {
            // Filters have been updated

            // Parse the selected values
            $newFilters     = [];
            $updatedFilters = json_decode($updatedFilters, true);

            if ($updatedFilters) {
                foreach ($updatedFilters as $updatedFilter) {
                    [$clmn, $fltr] = explode(':', $updatedFilter);

                    $newFilters[$clmn][] = $fltr;
                }

                $currentFilters = $newFilters;
            } else {
                $currentFilters = [];
            }
        }
        $session->set('milex.email.list_filters', $currentFilters);

        if (!empty($currentFilters)) {
            $listIds = $catIds = $templates = [];
            foreach ($currentFilters as $type => $typeFilters) {
                switch ($type) {
                    case 'list':
                        $key = 'lists';
                        break;
                    case 'category':
                        $key = 'categories';
                        break;
                    case 'theme':
                        $key = 'themes';
                        break;
                }

                $listFilters['filters']['groups']['milex.core.filter.'.$key]['values'] = $typeFilters;

                foreach ($typeFilters as $fltr) {
                    switch ($type) {
                        case 'list':
                            $listIds[] = (int) $fltr;
                            break;
                        case 'category':
                            $catIds[] = (int) $fltr;
                            break;
                        case 'theme':
                            $templates[] = $fltr;
                            break;
                    }
                }
            }

            if (!empty($listIds)) {
                $filter['force'][] = ['column' => 'l.id', 'expr' => 'in', 'value' => $listIds];
                $ignoreListJoin    = false;
            }

            if (!empty($catIds)) {
                $filter['force'][] = ['column' => 'c.id', 'expr' => 'in', 'value' => $catIds];
            }

            if (!empty($templates)) {
                $filter['force'][] = ['column' => 'e.template', 'expr' => 'in', 'value' => $templates];
            }
        }

        $orderBy    = $session->get('milex.email.orderby', 'e.dateModified');
        $orderByDir = $session->get('milex.email.orderbydir', $this->getDefaultOrderDirection());

        $emails = $model->getEntities(
            [
                'start'          => $start,
                'limit'          => $limit,
                'filter'         => $filter,
                'orderBy'        => $orderBy,
                'orderByDir'     => $orderByDir,
                'ignoreListJoin' => $ignoreListJoin,
            ]
        );

        $count = count($emails);
        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            if (1 === $count) {
                $lastPage = 1;
            } else {
                $lastPage = (floor($count / $limit)) ?: 1;
            }

            $session->set('milex.email.page', $lastPage);
            $returnUrl = $this->generateUrl('milex_email_index', ['page' => $lastPage]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $lastPage],
                    'contentTemplate' => 'MilexEmailBundle:Email:index',
                    'passthroughVars' => [
                        'activeLink'    => '#milex_email_index',
                        'milexContent' => 'email',
                    ],
                ]
            );
        }
        $session->set('milex.email.page', $page);

        return $this->delegateView(
            [
                'viewParameters' => [
                    'searchValue' => $search,
                    'filters'     => $listFilters,
                    'items'       => $emails,
                    'totalItems'  => $count,
                    'page'        => $page,
                    'limit'       => $limit,
                    'tmpl'        => $this->request->get('tmpl', 'index'),
                    'permissions' => $permissions,
                    'model'       => $model,
                ],
                'contentTemplate' => 'MilexEmailBundle:Email:list.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#milex_email_index',
                    'milexContent' => 'email',
                    'route'         => $this->generateUrl('milex_email_index', ['page' => $page]),
                ],
            ]
        );
    }

    /**
     * Loads a specific form into the detailed panel.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($objectId)
    {
        /** @var \Milex\EmailBundle\Model\EmailModel $model */
        $model    = $this->getModel('email');
        $security = $this->get('milex.security');

        /** @var \Milex\EmailBundle\Entity\Email $email */
        $email = $model->getEntity($objectId);
        //set the page we came from
        $page = $this->get('session')->get('milex.email.page', 1);

        // Init the date range filter form
        $dateRangeValues = $this->request->get('daterange', []);
        $action          = $this->generateUrl('milex_email_action', ['objectAction' => 'view', 'objectId' => $objectId]);
        $dateRangeForm   = $this->get('form.factory')->create(DateRangeType::class, $dateRangeValues, ['action' => $action]);

        if (null === $email) {
            //set the return URL
            $returnUrl = $this->generateUrl('milex_email_index', ['page' => $page]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'MilexEmailBundle:Email:index',
                    'passthroughVars' => [
                        'activeLink'    => '#milex_email_index',
                        'milexContent' => 'email',
                    ],
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'milex.email.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ]
            );
        } elseif (!$this->get('milex.security')->hasEntityAccess(
            'email:emails:viewown',
            'email:emails:viewother',
            $email->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        }

        //get A/B test information
        [$parent, $children]     = $email->getVariants();
        $properties              = [];
        $variantError            = false;
        $weight                  = 0;
        if (count($children)) {
            foreach ($children as $c) {
                $variantSettings = $c->getVariantSettings();

                if (is_array($variantSettings) && isset($variantSettings['winnerCriteria'])) {
                    if ($c->isPublished()) {
                        if (!isset($lastCriteria)) {
                            $lastCriteria = $variantSettings['winnerCriteria'];
                        }

                        //make sure all the variants are configured with the same criteria
                        if ($lastCriteria != $variantSettings['winnerCriteria']) {
                            $variantError = true;
                        }

                        $weight += $variantSettings['weight'];
                    }
                } else {
                    $variantSettings['winnerCriteria'] = '';
                    $variantSettings['weight']         = 0;
                }

                $properties[$c->getId()] = $variantSettings;
            }

            $properties[$parent->getId()]['weight']         = 100 - $weight;
            $properties[$parent->getId()]['winnerCriteria'] = '';
        }

        $abTestResults = [];
        $criteria      = $model->getBuilderComponents($email, 'abTestWinnerCriteria');
        if (!empty($lastCriteria) && empty($variantError)) {
            if (isset($criteria['criteria'][$lastCriteria])) {
                $testSettings = $criteria['criteria'][$lastCriteria];

                $args = [
                    'email'      => $email,
                    'parent'     => $parent,
                    'children'   => $children,
                    'properties' => $properties,
                ];

                $event = new DetermineWinnerEvent($args);
                $this->dispatcher->dispatch(
                        $testSettings['event'],
                        $event
                    );

                $abTestResults = $event->getAbTestResults();
            }
        }

        //get related translations
        [$translationParent, $translationChildren] = $email->getTranslations();

        // Audit Log
        $logs = $this->getModel('core.auditlog')->getLogForObject('email', $email->getId(), $email->getDateAdded());

        // Get click through stats
        $trackableLinks = $model->getEmailClickStats($email->getId());

        return $this->delegateView(
            [
                'returnUrl' => $this->generateUrl(
                    'milex_email_action',
                    [
                        'objectAction' => 'view',
                        'objectId'     => $email->getId(),
                    ]
                ),
                'viewParameters' => [
                    'email'        => $email,
                    'trackables'   => $trackableLinks,
                    'logs'         => $logs,
                    'isEmbedded'   => $this->request->get('isEmbedded') ? $this->request->get('isEmbedded') : false,
                    'variants'     => [
                        'parent'     => $parent,
                        'children'   => $children,
                        'properties' => $properties,
                        'criteria'   => $criteria['criteria'],
                    ],
                    'translations' => [
                        'parent'   => $translationParent,
                        'children' => $translationChildren,
                    ],
                    'permissions' => $security->isGranted(
                        [
                            'email:emails:viewown',
                            'email:emails:viewother',
                            'email:emails:create',
                            'email:emails:editown',
                            'email:emails:editother',
                            'email:emails:deleteown',
                            'email:emails:deleteother',
                            'email:emails:publishown',
                            'email:emails:publishother',
                        ],
                        'RETURN_ARRAY'
                    ),
                    'abTestResults' => $abTestResults,
                    'security'      => $security,
                    'previewUrl'    => $this->generateUrl(
                        'milex_email_preview',
                        ['objectId' => $email->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                    'contacts' => $this->forward(
                        'MilexEmailBundle:Email:contacts',
                        [
                            'objectId'   => $email->getId(),
                            'page'       => $this->get('session')->get('milex.email.contact.page', 1),
                            'ignoreAjax' => true,
                        ]
                    )->getContent(),
                    'dateRangeForm' => $dateRangeForm->createView(),
                ],
                'contentTemplate' => 'MilexEmailBundle:Email:details.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#milex_email_index',
                    'milexContent' => 'email',
                ],
            ]
        );
    }

    /**
     * Generates new form and processes post data.
     *
     * @param \Milex\EmailBundle\Entity\Email $entity
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction($entity = null)
    {
        $model = $this->getModel('email');

        if (!($entity instanceof Email)) {
            /** @var \Milex\EmailBundle\Entity\Email $entity */
            $entity = $model->getEntity();
        }

        $method  = $this->request->getMethod();
        $session = $this->get('session');

        if (!$this->get('milex.security')->isGranted('email:emails:create')) {
            return $this->accessDenied();
        }

        //set the page we came from
        $page         = $session->get('milex.email.page', 1);
        $action       = $this->generateUrl('milex_email_action', ['objectAction' => 'new']);
        $emailForm    = $this->request->request->get('emailform', []);
        $updateSelect = 'POST' === $method
            ? ($emailForm['updateSelect'] ?? false)
            : $this->request->get('updateSelect', false);

        if ($updateSelect) {
            // Force type to template
            $entity->setEmailType('template');
        }

        //create the form
        $form = $model->createForm($entity, $this->get('form.factory'), $action, ['update_select' => $updateSelect]);

        ///Check for a submitted form and process it
        if ('POST' === $method) {
            $valid = false;

            if (!$cancelled = $this->isFormCancelled($form)) {
                $formData = $this->request->request->get('emailform');
                if ($valid = $this->isFormValid($form) && $this->isFormValidForWebinar($formData, $form, $entity)) {
                    $content = $entity->getCustomHtml();

                    $entity->setCustomHtml($content);

                    //form is valid so process the data
                    $model->saveEntity($entity);

                    $this->addFlash(
                        'milex.core.notice.created',
                        [
                            '%name%'      => $entity->getName(),
                            '%menu_link%' => 'milex_email_index',
                            '%url%'       => $this->generateUrl(
                                'milex_email_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $entity->getId(),
                                ]
                            ),
                        ]
                    );

                    if ($form->get('buttons')->get('save')->isClicked()) {
                        $viewParameters = [
                            'objectAction' => 'view',
                            'objectId'     => $entity->getId(),
                        ];
                        $returnUrl = $this->generateUrl('milex_email_action', $viewParameters);
                        $template  = 'MilexEmailBundle:Email:view';
                    } else {
                        //return edit view so that all the session stuff is loaded
                        return $this->editAction($entity->getId(), true);
                    }
                }
            } else {
                $viewParameters = ['page' => $page];
                $returnUrl      = $this->generateUrl('milex_email_index', $viewParameters);
                $template       = 'MilexEmailBundle:Email:index';
                //clear any modified content
                $session->remove('milex.emailbuilder.'.$entity->getSessionId().'.content');
            }

            $passthrough = [
                'activeLink'    => 'milex_email_index',
                'milexContent' => 'email',
            ];

            // Check to see if this is a popup
            if (isset($form['updateSelect'])) {
                $template    = false;
                $passthrough = array_merge(
                    $passthrough,
                    [
                        'updateSelect' => $form['updateSelect']->getData(),
                        'id'           => $entity->getId(),
                        'name'         => $entity->getName(),
                        'group'        => $entity->getLanguage(),
                    ]
                );
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $returnUrl,
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $template,
                        'passthroughVars' => $passthrough,
                    ]
                );
            }
        }

        $slotTypes   = $model->getBuilderComponents($entity, 'slotTypes');
        $sections    = $model->getBuilderComponents($entity, 'sections');
        $sectionForm = $this->get('form.factory')->create(BuilderSectionType::class);
        $routeParams = [
            'objectAction' => 'new',
        ];
        if ($updateSelect) {
            $routeParams['updateSelect'] = $updateSelect;
            $routeParams['contentOnly']  = 1;
        }

        //set some permissions
        $permissions = $this->get('milex.security')->isGranted(
            [
                'page:preference_center:viewown',
                'page:preference_center:viewother',
            ],
            'RETURN_ARRAY'
        );

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'          => $this->setFormTheme($form, 'MilexEmailBundle:Email:form.html.php', 'MilexEmailBundle:FormTheme\Email'),
                    'isVariant'     => $entity->isVariant(true),
                    'email'         => $entity,
                    'slots'         => $this->buildSlotForms($slotTypes),
                    'sections'      => $this->buildSlotForms($sections),
                    'themes'        => $this->factory->getInstalledThemes('email', true),
                    'builderAssets' => trim(preg_replace('/\s+/', ' ', $this->getAssetsForBuilder())), // strip new lines
                    'sectionForm'   => $sectionForm->createView(),
                    'updateSelect'  => $updateSelect,
                    'permissions'   => $permissions,
                ],
                'contentTemplate' => 'MilexEmailBundle:Email:form.html.php',
                'passthroughVars' => [
                    'activeLink'      => '#milex_email_index',
                    'milexContent'   => 'email',
                    'updateSelect'    => $updateSelect,
                    'route'           => $this->generateUrl('milex_email_action', $routeParams),
                    'validationError' => $this->getFormErrorForBuilder($form),
                ],
            ]
        );
    }

    /**
     * @param      $objectId
     * @param bool $ignorePost
     * @param bool $forceTypeSelection
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction($objectId, $ignorePost = false, $forceTypeSelection = false)
    {
        /** @var \Milex\EmailBundle\Model\EmailModel $model */
        $model  = $this->getModel('email');
        $method = $this->request->getMethod();

        $entity  = $model->getEntity($objectId);
        $session = $this->get('session');
        $page    = $this->get('session')->get('milex.email.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('milex_email_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MilexEmailBundle:Email:index',
            'passthroughVars' => [
                'activeLink'    => 'milex_email_index',
                'milexContent' => 'email',
            ],
        ];

        //not found
        if (null === $entity) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'milex.email.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        } elseif (!$this->get('milex.security')->hasEntityAccess(
            'email:emails:editown',
            'email:emails:editother',
            $entity->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        } elseif ($model->isLocked($entity)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $entity, 'email');
        }

        //Create the form
        $action       = $this->generateUrl('milex_email_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $emailform    = $this->request->request->get('emailform', []);
        $updateSelect = 'POST' === $method
            ? ($emailform['updateSelect'] ?? false)
            : $this->request->get('updateSelect', false);

        if ($updateSelect) {
            // Force type to template
            $entity->setEmailType('template');
        }
        /** @var Form $form */
        $form = $model->createForm($entity, $this->get('form.factory'), $action, ['update_select' => $updateSelect]);

        ///Check for a submitted form and process it
        if (!$ignorePost && 'POST' === $method) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                $formData = $this->request->request->get('emailform');
                if ($valid = $this->isFormValid($form) && $this->isFormValidForWebinar($formData, $form, $entity)) {
                    $content = $entity->getCustomHtml();
                    $entity->setCustomHtml($content);

                    //form is valid so process the data
                    $model->saveEntity($entity, $form->get('buttons')->get('save')->isClicked());

                    $this->addFlash(
                        'milex.core.notice.updated',
                        [
                            '%name%'      => $entity->getName(),
                            '%menu_link%' => 'milex_email_index',
                            '%url%'       => $this->generateUrl(
                                'milex_email_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $entity->getId(),
                                ]
                            ),
                        ]
                    );
                }
            } else {
                //clear any modified content
                $session->remove('milex.emailbuilder.'.$objectId.'.content');
                //unlock the entity
                $model->unlockEntity($entity);
            }

            $template    = 'MilexEmailBundle:Email:view';
            $passthrough = [
                'activeLink'    => 'milex_email_index',
                'milexContent' => 'email',
            ];

            // Check to see if this is a popup
            if (isset($form['updateSelect'])) {
                $template    = false;
                $passthrough = array_merge(
                    $passthrough,
                    [
                        'updateSelect' => $form['updateSelect']->getData(),
                        'id'           => $entity->getId(),
                        'name'         => $entity->getName(),
                        'group'        => $entity->getLanguage(),
                    ]
                );
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                $viewParameters = [
                    'objectAction' => 'view',
                    'objectId'     => $entity->getId(),
                ];

                return $this->postActionRedirect(
                    array_merge(
                        $postActionVars,
                        [
                            'returnUrl'       => $this->generateUrl('milex_email_action', $viewParameters),
                            'viewParameters'  => $viewParameters,
                            'contentTemplate' => $template,
                            'passthroughVars' => $passthrough,
                        ]
                    )
                );
            } elseif ($valid && $form->get('buttons')->get('apply')->isClicked()) {
                // Rebuild the form in the case apply is clicked so that DEC content is properly populated if all were removed
                $form = $model->createForm($entity, $this->get('form.factory'), $action, ['update_select' => $updateSelect]);
            }
        } else {
            //lock the entity
            $model->lockEntity($entity);

            //clear any modified content
            $session->remove('milex.emailbuilder.'.$objectId.'.content');

            // Set to view content
            $template = $entity->getTemplate();
            if (empty($template)) {
                $content = $entity->getCustomHtml();
                $form['customHtml']->setData($content);
            }
        }

        $assets         = $form['assetAttachments']->getData();
        $attachmentSize = $this->getModel('asset')->getTotalFilesize($assets);

        $slotTypes   = $model->getBuilderComponents($entity, 'slotTypes');
        $sections    = $model->getBuilderComponents($entity, 'sections');
        $sectionForm = $this->get('form.factory')->create(BuilderSectionType::class);
        $routeParams = [
            'objectAction' => 'edit',
            'objectId'     => $entity->getId(),
        ];
        if ($updateSelect) {
            $routeParams['updateSelect'] = $updateSelect;
            $routeParams['contentOnly']  = 1;
        }

        //set some permissions
        $permissions = $this->get('milex.security')->isGranted(
            [
                'page:preference_center:viewown',
                'page:preference_center:viewother',
            ],
            'RETURN_ARRAY'
        );

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'               => $this->setFormTheme($form, 'MilexEmailBundle:Email:form.html.php', 'MilexEmailBundle:FormTheme\Email'),
                    'isVariant'          => $entity->isVariant(true),
                    'slots'              => $this->buildSlotForms($slotTypes),
                    'sections'           => $this->buildSlotForms($sections),
                    'themes'             => $this->factory->getInstalledThemes('email', true),
                    'email'              => $entity,
                    'forceTypeSelection' => $forceTypeSelection,
                    'attachmentSize'     => $attachmentSize,
                    'builderAssets'      => trim(preg_replace('/\s+/', ' ', $this->getAssetsForBuilder())), // strip new lines
                    'sectionForm'        => $sectionForm->createView(),
                    'permissions'        => $permissions,
                    'previewUrl'         => $this->generateUrl(
                        'milex_email_preview',
                        ['objectId' => $entity->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                ],
                'contentTemplate' => 'MilexEmailBundle:Email:form.html.php',
                'passthroughVars' => [
                    'activeLink'      => '#milex_email_index',
                    'milexContent'   => 'email',
                    'updateSelect'    => InputHelper::clean($this->request->query->get('updateSelect')),
                    'route'           => $this->generateUrl('milex_email_action', $routeParams),
                    'validationError' => $this->getFormErrorForBuilder($form),
                ],
            ]
        );
    }

    /**
     * Clone an entity.
     *
     * @param $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function cloneAction($objectId)
    {
        $model = $this->getModel('email');
        /** @var Email $entity */
        $entity = $model->getEntity($objectId);

        if (null != $entity) {
            if (!$this->get('milex.security')->isGranted('email:emails:create')
                || !$this->get('milex.security')->hasEntityAccess(
                    'email:emails:viewown',
                    'email:emails:viewother',
                    $entity->getCreatedBy()
                )
            ) {
                return $this->accessDenied();
            }

            $entity      = clone $entity;
            $session     = $this->get('session');
            $contentName = 'milex.emailbuilder.'.$entity->getSessionId().'.content';

            $session->set($contentName, $entity->getCustomHtml());
        }

        return $this->newAction($entity);
    }

    /**
     * Deletes the entity.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($objectId)
    {
        $page      = $this->get('session')->get('milex.email.page', 1);
        $returnUrl = $this->generateUrl('milex_email_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MilexEmailBundle:Email:index',
            'passthroughVars' => [
                'activeLink'    => 'milex_email_index',
                'milexContent' => 'email',
            ],
        ];

        if ('POST' == $this->request->getMethod()) {
            $model  = $this->getModel('email');
            $entity = $model->getEntity($objectId);

            if (null === $entity) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'milex.email.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('milex.security')->hasEntityAccess(
                'email:emails:deleteown',
                'email:emails:deleteother',
                $entity->getCreatedBy()
            )
            ) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'email');
            }

            $model->deleteEntity($entity);

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'milex.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $entity->getName(),
                    '%id%'   => $objectId,
                ],
            ];
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                [
                    'flashes' => $flashes,
                ]
            )
        );
    }

    /**
     * Activate the builder.
     *
     * @param $objectId
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     *
     * @throws \Exception
     * @throws \Milex\CoreBundle\Exception\FileNotFoundException
     */
    public function builderAction($objectId)
    {
        /** @var \Milex\EmailBundle\Model\EmailModel $model */
        $model = $this->getModel('email');

        //permission check
        if (false !== strpos($objectId, 'new')) {
            $isNew = true;
            if (!$this->get('milex.security')->isGranted('email:emails:create')) {
                return $this->accessDenied();
            }
            $entity = $model->getEntity();
            $entity->setSessionId($objectId);
        } else {
            $isNew  = false;
            $entity = $model->getEntity($objectId);
            if (null == $entity
                || !$this->get('milex.security')->hasEntityAccess(
                    'email:emails:viewown',
                    'email:emails:viewother',
                    $entity->getCreatedBy()
                )
            ) {
                return $this->accessDenied();
            }
        }

        $template = InputHelper::clean($this->request->query->get('template'));
        $slots    = $this->factory->getTheme($template)->getSlots('email');

        //merge any existing changes
        $newContent = $this->get('session')->get('milex.emailbuilder.'.$objectId.'.content', []);
        $content    = $entity->getContent();

        if (is_array($newContent)) {
            $content = array_merge($content, $newContent);
            // Update the content for processSlots
            $entity->setContent($content);
        }

        // Replace short codes to emoji
        $content = EmojiHelper::toEmoji($content, 'short');

        $this->processSlots($slots, $entity);

        $logicalName = $this->factory->getHelper('theme')->checkForTwigTemplate(':'.$template.':email.html.php');

        return $this->render(
            $logicalName,
            [
                'isNew'    => $isNew,
                'slots'    => $slots,
                'content'  => $content,
                'email'    => $entity,
                'template' => $template,
                'basePath' => $this->request->getBasePath(),
            ]
        );
    }

    /**
     * Create an AB test.
     *
     * @param $objectId
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function abtestAction($objectId)
    {
        $model  = $this->getModel('email');
        $entity = $model->getEntity($objectId);

        if (null != $entity) {
            $parent = $entity->getVariantParent();

            if ($parent || !$this->get('milex.security')->isGranted('email:emails:create')
                || !$this->get('milex.security')->hasEntityAccess(
                    'email:emails:viewown',
                    'email:emails:viewother',
                    $entity->getCreatedBy()
                )
            ) {
                return $this->accessDenied();
            }

            // Note this since it's cleared on __clone()
            $emailType = $entity->getEmailType();

            $clone = clone $entity;

            $clone->setIsPublished(false);
            $clone->setEmailType($emailType);
            $clone->setVariantParent($entity);
        }

        return $this->newAction($clone);
    }

    /**
     * Make the variant the main.
     *
     * @param $objectId
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function winnerAction($objectId)
    {
        //todo - add confirmation to button click
        $page      = $this->get('session')->get('milex.email', 1);
        $returnUrl = $this->generateUrl('milex_email_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MilexEmailBundle:Page:index',
            'passthroughVars' => [
                'activeLink'    => 'milex_email_index',
                'milexContent' => 'page',
            ],
        ];

        if ('POST' == $this->request->getMethod()) {
            $model  = $this->getModel('email');
            $entity = $model->getEntity($objectId);

            if (null === $entity) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'milex.email.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('milex.security')->hasEntityAccess(
                'email:emails:editown',
                'email:emails:editother',
                $entity->getCreatedBy()
            )
            ) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'email');
            }

            $model->convertVariant($entity);

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'milex.email.notice.activated',
                'msgVars' => [
                    '%name%' => $entity->getName(),
                    '%id%'   => $objectId,
                ],
            ];

            $postActionVars['viewParameters'] = [
                'objectAction' => 'view',
                'objectId'     => $objectId,
            ];
            $postActionVars['returnUrl']       = $this->generateUrl('milex_page_action', $postActionVars['viewParameters']);
            $postActionVars['contentTemplate'] = 'MilexEmailBundle:Email:view';
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                [
                    'flashes' => $flashes,
                ]
            )
        );
    }

    /**
     * Manually sends emails.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function sendAction($objectId)
    {
        /** @var \Milex\EmailBundle\Model\EmailModel $model */
        $model   = $this->getModel('email');
        $entity  = $model->getEntity($objectId);
        $session = $this->container->get('session');
        $page    = $session->get('milex.email.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('milex_email_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MilexEmailBundle:Email:index',
            'passthroughVars' => [
                'activeLink'    => 'milex_email_index',
                'milexContent' => 'email',
            ],
        ];

        //not found
        if (null === $entity) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'milex.email.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        }

        if (!$entity->isPublished()) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'milex.email.error.send.unpublished',
                                'msgVars' => [
                                    '%id%'   => $objectId,
                                    '%name%' => $entity->getName(),
                                ],
                            ],
                        ],
                    ]
                )
            );
        }

        if ('template' == $entity->getEmailType()
            || !$this->get('milex.security')->hasEntityAccess(
                'email:emails:viewown',
                'email:emails:viewother',
                $entity->getCreatedBy()
            )
        ) {
            return $this->accessDenied();
        }

        // Check that the parent is getting sent
        if ($variantParent = $entity->getVariantParent()) {
            return $this->redirect($this->generateUrl('milex_email_action',
                [
                    'objectAction' => 'send',
                    'objectId'     => $variantParent->getId(),
                ]
            ));
        }

        if ($translationParent = $entity->getTranslationParent()) {
            return $this->redirect($this->generateUrl('milex_email_action',
                [
                    'objectAction' => 'send',
                    'objectId'     => $translationParent->getId(),
                ]
            ));
        }

        $action   = $this->generateUrl('milex_email_action', ['objectAction' => 'send', 'objectId' => $objectId]);
        $pending  = $model->getPendingLeads($entity, null, true);
        $form     = $this->get('form.factory')->create(BatchSendType::class, [], ['action' => $action]);
        $complete = $this->request->request->get('complete', false);

        if ('POST' == $this->request->getMethod() && ($complete || $this->isFormValid($form))) {
            if (!$complete) {
                $progress = [0, (int) $pending];
                $session->set('milex.email.send.progress', $progress);

                $stats = ['sent' => 0, 'failed' => 0, 'failedRecipients' => []];
                $session->set('milex.email.send.stats', $stats);

                $status     = 'inprogress';
                $batchlimit = $form['batchlimit']->getData();

                $session->set('milex.email.send.active', false);
            } else {
                $stats      = $session->get('milex.email.send.stats');
                $progress   = $session->get('milex.email.send.progress');
                $batchlimit = 100;
                $status     = (!empty($stats['failed'])) ? 'with_errors' : 'success';
            }

            $contentTemplate = 'MilexEmailBundle:Send:progress.html.php';
            $viewParameters  = [
                'progress'   => $progress,
                'stats'      => $stats,
                'status'     => $status,
                'email'      => $entity,
                'batchlimit' => $batchlimit,
            ];
        } else {
            //process and send
            $contentTemplate = 'MilexEmailBundle:Send:form.html.php';
            $viewParameters  = [
                'form'    => $form->createView(),
                'email'   => $entity,
                'pending' => $pending,
            ];
        }

        return $this->delegateView(
            [
                'viewParameters'  => $viewParameters,
                'contentTemplate' => $contentTemplate,
                'passthroughVars' => [
                    'milexContent' => 'emailSend',
                    'route'         => $action,
                ],
            ]
        );
    }

    /**
     * Deletes a group of entities.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        $page      = $this->get('session')->get('milex.email.page', 1);
        $returnUrl = $this->generateUrl('milex_email_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MilexEmailBundle:Email:index',
            'passthroughVars' => [
                'activeLink'    => '#milex_email_index',
                'milexContent' => 'email',
            ],
        ];

        if ('POST' == $this->request->getMethod()) {
            $model = $this->getModel('email');
            $ids   = json_decode($this->request->query->get('ids', '{}'));

            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if (null === $entity) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'milex.email.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('milex.security')->hasEntityAccess(
                    'email:emails:viewown',
                    'email:emails:viewother',
                    $entity->getCreatedBy()
                )
                ) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'email', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'milex.email.notice.batch_deleted',
                    'msgVars' => [
                        '%count%' => count($entities),
                    ],
                ];
            }
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                [
                    'flashes' => $flashes,
                ]
            )
        );
    }

    /**
     * Generating the modal box content for
     * the send multiple example email option.
     */
    public function sendExampleAction($objectId)
    {
        $model  = $this->getModel('email');
        /** @var Email $entity */
        $entity = $model->getEntity($objectId);

        //not found or not allowed
        if (null === $entity
            || (!$this->get('milex.security')->hasEntityAccess(
                'email:emails:viewown',
                'email:emails:viewother',
                $entity->getCreatedBy()
            ))
        ) {
            return $this->postActionRedirect(
                [
                    'passthroughVars' => [
                        'closeModal' => 1,
                        'route'      => false,
                    ],
                ]
            );
        }

        // Get the quick add form
        $action = $this->generateUrl('milex_email_action', ['objectAction' => 'sendExample', 'objectId' => $objectId]);
        $user   = $this->get('milex.helper.user')->getUser();

        // We have to add prefix to example emails
        $subject = sprintf('%s %s', static::EXAMPLE_EMAIL_SUBJECT_PREFIX, $entity->getSubject());
        $entity->setSubject($subject);

        $form = $this->createForm(ExampleSendType::class, ['emails' => ['list' => [$user->getEmail()]]], ['action' => $action]);
        /* @var \Milex\EmailBundle\Model\EmailModel $model */

        if ('POST' == $this->request->getMethod()) {
            $isCancelled = $this->isFormCancelled($form);
            $isValid     = $this->isFormValid($form);
            if (!$isCancelled && $isValid) {
                $emails = $form['emails']->getData()['list'];

                // Prepare a fake lead
                /** @var \Milex\LeadBundle\Model\FieldModel $fieldModel */
                $fieldModel = $this->getModel('lead.field');
                $fields     = $fieldModel->getFieldList(false, false);
                array_walk(
                    $fields,
                    function (&$field) {
                        $field = "[$field]";
                    }
                );
                $fields['id'] = 0;

                $errors = [];
                foreach ($emails as $email) {
                    if (!empty($email)) {
                        $users = [
                            [
                                // Setting the id, firstname and lastname to null as this is a unknown user
                                'id'        => '',
                                'firstname' => '',
                                'lastname'  => '',
                                'email'     => $email,
                            ],
                        ];

                        // Send to current user
                        $error = $model->sendSampleEmailToUser($entity, $users, $fields, [], [], false);
                        if (count($error)) {
                            array_push($errors, $error[0]);
                        }
                    }
                }

                if (0 != count($errors)) {
                    $this->addFlash(implode('; ', $errors));
                } else {
                    $this->addFlash('milex.email.notice.test_sent_multiple.success');
                }
            }

            if ($isValid || $isCancelled) {
                return $this->postActionRedirect(
                    [
                        'passthroughVars' => [
                            'closeModal' => 1,
                            'route'      => false,
                        ],
                    ]
                );
            }
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form' => $form->createView(),
                ],
                'contentTemplate' => 'MilexEmailBundle:Email:recipients.html.php',
            ]
        );
    }

    /**
     * PreProcess page slots for public view.
     *
     * @param array $slots
     * @param Email $entity
     */
    private function processSlots($slots, $entity)
    {
        /** @var \Milex\CoreBundle\Templating\Helper\SlotsHelper $slotsHelper */
        $slotsHelper = $this->get('templating.helper.slots');
        $content     = $entity->getContent();

        //Set the slots
        foreach ($slots as $slot => $slotConfig) {
            //support previous format where email slots are not defined with config array
            if (is_numeric($slot)) {
                $slot       = $slotConfig;
                $slotConfig = [];
            }

            $value = isset($content[$slot]) ? $content[$slot] : '';
            $slotsHelper->set($slot, "<div data-slot=\"text\" id=\"slot-{$slot}\">{$value}</div>");
        }

        //add builder toolbar
        $slotsHelper->start('builder'); ?>
        <input type="hidden" id="builder_entity_id" value="<?php echo $entity->getSessionId(); ?>"/>
        <?php
        $slotsHelper->stop();
    }

    /**
     * Checks the form data for webinar tokens and validates that the segment has webinar filters.
     *
     * @return int
     */
    protected function isFormValidForWebinar(array $data, Form &$form, Email $email)
    {
        if (!CitrixHelper::isAuthorized('Gotowebinar')) {
            return true;
        }

        // search for webinar filters in the email segments
        if (!array_key_exists('lists', $data) || 0 === count($data['lists'])) {
            return true;
        }

        // search for token in content
        $html         = $email->getCustomHtml();
        $isTokenFound = preg_match('/\{webinar_button\}/', $html);
        if (!$isTokenFound) {
            return true;
        }

        $isWebinarFilterPresent = false;
        $webinarFiltersCount    = 0;
        $lists                  = $data['lists'];
        /** @var ListModel $model */
        $model = $this->getModel('lead.list');
        foreach ($lists as $listId) {
            $list    = $model->getEntity($listId);
            $filters = $list->getFilters();
            foreach ($filters as $filter) {
                if ('webinar-registration' == $filter['field'] && 'in' == $filter['operator']) {
                    $isWebinarFilterPresent = true;
                    ++$webinarFiltersCount;
                }
            }
        }
        // make sure that each list has a webinar-registration filter
        if (count($lists) !== $webinarFiltersCount) {
            $isWebinarFilterPresent = false;
        }
        if (!$isWebinarFilterPresent) {
            $error = $this->get('translator')->trans('plugin.citrix.webinar.token_error');
            $form->addError(new FormError($error));

            return false;
        }

        // everything is ok
        return true;
    }

    /**
     * @param     $objectId
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function contactsAction($objectId, $page = 1)
    {
        return $this->generateContactsGrid(
            $objectId,
            $page,
            ['email:emails:viewown', 'email:emails:viewother'],
            'email',
            'email_stats',
            'email',
            'email_id'
        );
    }

    public function getModelName(): string
    {
        return 'email';
    }

    protected function getDefaultOrderDirection(): string
    {
        return 'DESC';
    }
}
