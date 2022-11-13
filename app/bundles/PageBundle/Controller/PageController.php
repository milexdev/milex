<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Milex\PageBundle\Controller;

use Milex\CoreBundle\Controller\BuilderControllerTrait;
use Milex\CoreBundle\Controller\FormController;
use Milex\CoreBundle\Controller\FormErrorMessagesTrait;
use Milex\CoreBundle\Event\DetermineWinnerEvent;
use Milex\CoreBundle\Factory\PageHelperFactoryInterface;
use Milex\CoreBundle\Form\Type\BuilderSectionType;
use Milex\CoreBundle\Form\Type\DateRangeType;
use Milex\CoreBundle\Helper\InputHelper;
use Milex\PageBundle\Entity\Page;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PageController extends FormController
{
    use BuilderControllerTrait;
    use FormErrorMessagesTrait;

    /**
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page = 1)
    {
        $model = $this->getModel('page.page');

        //set some permissions
        $permissions = $this->get('milex.security')->isGranted([
            'page:pages:viewown',
            'page:pages:viewother',
            'page:pages:create',
            'page:pages:editown',
            'page:pages:editother',
            'page:pages:deleteown',
            'page:pages:deleteother',
            'page:pages:publishown',
            'page:pages:publishother',
            'page:preference_center:viewown',
            'page:preference_center:viewother',
        ], 'RETURN_ARRAY');

        if (!$permissions['page:pages:viewown'] && !$permissions['page:pages:viewother']) {
            return $this->accessDenied();
        }

        $this->setListFilters();

        /** @var PageHelperFactoryInterface $pageHelperFacotry */
        $pageHelperFacotry = $this->get('milex.page.helper.factory');
        $pageHelper        = $pageHelperFacotry->make('milex.page', $page);

        $limit  = $pageHelper->getLimit();
        $start  = $pageHelper->getStart();
        $search = $this->request->get('search', $this->get('session')->get('milex.page.filter', ''));
        $filter = ['string' => $search, 'force' => []];

        $this->get('session')->set('milex.page.filter', $search);

        if (!$permissions['page:pages:viewother']) {
            $filter['force'][] = ['column' => 'p.createdBy', 'expr' => 'eq', 'value' => $this->user->getId()];
        }

        if (!$permissions['page:preference_center:viewown'] && !$permissions['page:preference_center:viewother']) {
            $filter['where'][] = [
                'expr' => 'orX',
                'val'  => [
                    ['column' => 'p.isPreferenceCenter', 'expr' => 'isNull'],
                    ['column' => 'p.isPreferenceCenter', 'expr' => 'eq', 'value' => 0],
                ],
            ];
        } elseif (!$permissions['page:preference_center:viewother']) {
            $filter['where'][] = [
                'expr' => 'orX',
                'val'  => [
                        [
                            'expr' => 'orX',
                            'val'  => [
                                ['column' => 'p.isPreferenceCenter', 'expr' => 'isNull'],
                                ['column' => 'p.isPreferenceCenter', 'expr' => 'eq', 'value' => 0],
                            ],
                        ],
                        [
                            'expr' => 'andX',
                            'val'  => [
                                ['column' => 'p.isPreferenceCenter', 'expr' => 'eq', 'value' => 1],
                                ['column' => 'p.createdBy', 'expr' => 'eq', 'value' => $this->user->getId()],
                            ],
                        ],
                    ],
                ];
        }

        $translator = $this->get('translator');

        //do not list variants in the main list
        $filter['force'][] = ['column' => 'p.variantParent', 'expr' => 'isNull'];

        $langSearchCommand = $translator->trans('milex.core.searchcommand.lang');
        if (false === strpos($search, "{$langSearchCommand}:")) {
            $filter['force'][] = ['column' => 'p.translationParent', 'expr' => 'isNull'];
        }

        $orderBy    = $this->get('session')->get('milex.page.orderby', 'p.dateModified');
        $orderByDir = $this->get('session')->get('milex.page.orderbydir', $this->getDefaultOrderDirection());
        $pages      = $model->getEntities(
            [
                'start'      => $start,
                'limit'      => $limit,
                'filter'     => $filter,
                'orderBy'    => $orderBy,
                'orderByDir' => $orderByDir,
            ]);

        $count = count($pages);
        if ($count && $count < ($start + 1)) {
            $lastPage  = $pageHelper->countPage($count);
            $returnUrl = $this->generateUrl('milex_page_index', ['page' => $lastPage]);
            $pageHelper->rememberPage($lastPage);

            return $this->postActionRedirect([
                'returnUrl'       => $returnUrl,
                'viewParameters'  => ['page' => $lastPage],
                'contentTemplate' => 'MilexPageBundle:Page:index',
                'passthroughVars' => [
                    'activeLink'    => '#milex_page_index',
                    'milexContent' => 'page',
                ],
            ]);
        }

        $pageHelper->rememberPage($page);

        return $this->delegateView([
            'viewParameters' => [
                'searchValue' => $search,
                'items'       => $pages,
                'categories'  => $this->getModel('page.page')->getLookupResults('category', '', 0),
                'page'        => $page,
                'limit'       => $limit,
                'permissions' => $permissions,
                'model'       => $model,
                'tmpl'        => $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index',
                'security'    => $this->get('milex.security'),
            ],
            'contentTemplate' => 'MilexPageBundle:Page:list.html.php',
            'passthroughVars' => [
                'activeLink'    => '#milex_page_index',
                'milexContent' => 'page',
                'route'         => $this->generateUrl('milex_page_index', ['page' => $page]),
            ],
        ]);
    }

    /**
     * Loads a specific form into the detailed panel.
     *
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($objectId)
    {
        /** @var \Milex\PageBundle\Model\PageModel $model */
        $model = $this->getModel('page.page');
        //set some permissions
        $security   = $this->get('milex.security');
        $activePage = $model->getEntity($objectId);
        //set the page we came from
        $page = $this->get('session')->get('milex.page.page', 1);

        if (null === $activePage) {
            //set the return URL
            $returnUrl = $this->generateUrl('milex_page_index', ['page' => $page]);

            return $this->postActionRedirect([
                'returnUrl'       => $returnUrl,
                'viewParameters'  => ['page' => $page],
                'contentTemplate' => 'MilexPageBundle:Page:index',
                'passthroughVars' => [
                    'activeLink'    => '#milex_page_index',
                    'milexContent' => 'page',
                ],
                'flashes' => [
                    [
                        'type'    => 'error',
                        'msg'     => 'milex.page.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ],
                ],
            ]);
        } elseif (!$security->hasEntityAccess(
                'page:pages:viewown', 'page:pages:viewother', $activePage->getCreatedBy()
            ) ||
            ($activePage->getIsPreferenceCenter() &&
                !$security->hasEntityAccess(
                    'page:preference_center:viewown', 'page:preference_center:viewother', $activePage->getCreatedBy()
                ))) {
            return $this->accessDenied();
        }

        //get A/B test information
        [$parent, $children]     = $activePage->getVariants();
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
        $criteria      = $model->getBuilderComponents($activePage, 'abTestWinnerCriteria');
        if (!empty($lastCriteria) && empty($variantError)) {
            //there is a criteria to compare the pages against so let's shoot the page over to the criteria function to do its thing
            if (isset($criteria['criteria'][$lastCriteria])) {
                $testSettings = $criteria['criteria'][$lastCriteria];

                $args = [
                    'page'       => $activePage,
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

        // Init the date range filter form
        $dateRangeValues = $this->request->get('daterange', []);
        $action          = $this->generateUrl('milex_page_action', ['objectAction' => 'view', 'objectId' => $objectId]);
        $dateRangeForm   = $this->get('form.factory')->create(DateRangeType::class, $dateRangeValues, ['action' => $action]);

        // Audit Log
        $logs = $this->getModel('core.auditlog')->getLogForObject('page', $activePage->getId(), $activePage->getDateAdded());

        $pageviews = $model->getHitsLineChartData(
            null,
            new \DateTime($dateRangeForm->get('date_from')->getData()),
            new \DateTime($dateRangeForm->get('date_to')->getData()),
            null,
            ['page_id' => $activePage->getId(), 'flag' => 'total_and_unique']
        );

        //get related translations
        [$translationParent, $translationChildren] = $activePage->getTranslations();

        return $this->delegateView([
            'returnUrl' => $this->generateUrl('milex_page_action', [
                    'objectAction' => 'view',
                    'objectId'     => $activePage->getId(), ]
            ),
            'viewParameters' => [
                'activePage' => $activePage,
                'variants'   => [
                    'parent'     => $parent,
                    'children'   => $children,
                    'properties' => $properties,
                    'criteria'   => $criteria['criteria'],
                ],
                'translations' => [
                    'parent'   => $translationParent,
                    'children' => $translationChildren,
                ],
                'permissions' => $security->isGranted([
                    'page:pages:viewown',
                    'page:pages:viewother',
                    'page:pages:create',
                    'page:pages:editown',
                    'page:pages:editother',
                    'page:pages:deleteown',
                    'page:pages:deleteother',
                    'page:pages:publishown',
                    'page:pages:publishother',
                    'page:preference_center:viewown',
                    'page:preference_center:viewother',
                ], 'RETURN_ARRAY'),
                'stats' => [
                    'pageviews' => $pageviews,
                    'hits'      => [
                        'total'  => $activePage->getHits(),
                        'unique' => $activePage->getUniqueHits(),
                    ],
                ],
                'abTestResults' => $abTestResults,
                'security'      => $security,
                'pageUrl'       => $model->generateUrl($activePage, true),
                'previewUrl'    => $this->generateUrl('milex_page_preview', ['id' => $objectId], UrlGeneratorInterface::ABSOLUTE_URL),
                'logs'          => $logs,
                'dateRangeForm' => $dateRangeForm->createView(),
            ],
            'contentTemplate' => 'MilexPageBundle:Page:details.html.php',
            'passthroughVars' => [
                'activeLink'    => '#milex_page_index',
                'milexContent' => 'page',
            ],
        ]);
    }

    /**
     * Generates new form and processes post data.
     *
     * @param \Milex\PageBundle\Entity\Page|null $entity
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction($entity = null)
    {
        /** @var \Milex\PageBundle\Model\PageModel $model */
        $model = $this->getModel('page.page');

        if (!($entity instanceof Page)) {
            /** @var \Milex\PageBundle\Entity\Page $entity */
            $entity = $model->getEntity();
        }

        $method  = $this->request->getMethod();
        $session = $this->get('session');
        if (!$this->get('milex.security')->isGranted('page:pages:create')) {
            return $this->accessDenied();
        }

        //set the page we came from
        $page   = $session->get('milex.page.page', 1);
        $action = $this->generateUrl('milex_page_action', ['objectAction' => 'new']);

        //create the form
        $form = $model->createForm($entity, $this->get('form.factory'), $action);

        ///Check for a submitted form and process it
        if ('POST' == $method) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $content = $entity->getCustomHtml();
                    $entity->setCustomHtml($content);
                    $entity->setDateModified(new \DateTime());

                    //form is valid so process the data
                    $model->saveEntity($entity);

                    $this->addFlash('milex.core.notice.created', [
                        '%name%'      => $entity->getTitle(),
                        '%menu_link%' => 'milex_page_index',
                        '%url%'       => $this->generateUrl('milex_page_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]),
                    ]);

                    if ($form->get('buttons')->get('save')->isClicked()) {
                        $viewParameters = [
                            'objectAction' => 'view',
                            'objectId'     => $entity->getId(),
                        ];
                        $returnUrl = $this->generateUrl('milex_page_action', $viewParameters);
                        $template  = 'MilexPageBundle:Page:view';
                    } else {
                        //return edit view so that all the session stuff is loaded
                        return $this->editAction($entity->getId(), true);
                    }
                }
            } else {
                $viewParameters = ['page' => $page];
                $returnUrl      = $this->generateUrl('milex_page_index', $viewParameters);
                $template       = 'MilexPageBundle:Page:index';
                //clear any modified content
                $session->remove('milex.pagebuilder.'.$entity->getSessionId().'.content');
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect([
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => $viewParameters,
                    'contentTemplate' => $template,
                    'passthroughVars' => [
                        'activeLink'    => 'milex_page_index',
                        'milexContent' => 'page',
                    ],
                ]);
            }
        }

        $slotTypes   = $model->getBuilderComponents($entity, 'slotTypes');
        $sections    = $model->getBuilderComponents($entity, 'sections');
        $sectionForm = $this->get('form.factory')->create(BuilderSectionType::class);

        //set some permissions
        $permissions = $this->get('milex.security')->isGranted(
            [
                'page:preference_center:editown',
                'page:preference_center:editother',
            ],
            'RETURN_ARRAY'
        );

        return $this->delegateView([
            'viewParameters' => [
                'form'          => $this->setFormTheme($form, 'MilexPageBundle:Page:form.html.php', 'MilexPageBundle:FormTheme\Page'),
                'isVariant'     => $entity->isVariant(true),
                'tokens'        => $model->getBuilderComponents($entity, 'tokens'),
                'activePage'    => $entity,
                'themes'        => $this->factory->getInstalledThemes('page', true),
                'slots'         => $this->buildSlotForms($slotTypes),
                'sections'      => $this->buildSlotForms($sections),
                'builderAssets' => trim(preg_replace('/\s+/', ' ', $this->getAssetsForBuilder())), // strip new lines
                'sectionForm'   => $sectionForm->createView(),
                'permissions'   => $permissions,
            ],
            'contentTemplate' => 'MilexPageBundle:Page:form.html.php',
            'passthroughVars' => [
                'activeLink'    => '#milex_page_index',
                'milexContent' => 'page',
                'route'         => $this->generateUrl('milex_page_action', [
                    'objectAction' => 'new',
                ]),
                'validationError' => $this->getFormErrorForBuilder($form),
            ],
        ]);
    }

    /**
     * Generates edit form and processes post data.
     *
     * @param int  $objectId
     * @param bool $ignorePost
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($objectId, $ignorePost = false)
    {
        /** @var \Milex\PageBundle\Model\PageModel $model */
        $model    = $this->getModel('page.page');
        $security = $this->get('milex.security');
        $entity   = $model->getEntity($objectId);
        $session  = $this->get('session');
        $page     = $this->get('session')->get('milex.page.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('milex_page_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MilexPageBundle:Page:index',
            'passthroughVars' => [
                'activeLink'    => 'milex_page_index',
                'milexContent' => 'page',
            ],
        ];

        //not found
        if (null === $entity) {
            return $this->postActionRedirect(
                array_merge($postActionVars, [
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'milex.page.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ])
            );
        } elseif (!$security->hasEntityAccess(
            'page:pages:viewown', 'page:pages:viewother', $entity->getCreatedBy()
        ) ||
            ($entity->getIsPreferenceCenter() && !$security->hasEntityAccess(
                    'page:preference_center:viewown', 'page:preference_center:viewother', $entity->getCreatedBy()
                ))) {
            return $this->accessDenied();
        } elseif ($model->isLocked($entity)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $entity, 'page.page');
        }

        //Create the form
        $action = $this->generateUrl('milex_page_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $form   = $model->createForm($entity, $this->get('form.factory'), $action);

        ///Check for a submitted form and process it
        if (!$ignorePost && 'POST' == $this->request->getMethod()) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $content = $entity->getCustomHtml();
                    $entity->setCustomHtml($content);

                    //form is valid so process the data
                    $model->saveEntity($entity, $form->get('buttons')->get('save')->isClicked());

                    $this->addFlash('milex.core.notice.updated', [
                        '%name%'      => $entity->getTitle(),
                        '%menu_link%' => 'milex_page_index',
                        '%url%'       => $this->generateUrl('milex_page_action', [
                            'objectAction' => 'edit',
                            'objectId'     => $entity->getId(),
                        ]),
                    ]);
                }
            } else {
                //clear any modified content
                $session->remove('milex.pagebuilder.'.$objectId.'.content');
                //unlock the entity
                $model->unlockEntity($entity);
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                $viewParameters = [
                    'objectAction' => 'view',
                    'objectId'     => $entity->getId(),
                ];

                return $this->postActionRedirect(
                    array_merge($postActionVars, [
                        'returnUrl'       => $this->generateUrl('milex_page_action', $viewParameters),
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => 'MilexPageBundle:Page:view',
                    ])
                );
            }
        } else {
            //lock the entity
            $model->lockEntity($entity);

            //clear any modified content
            $session->remove('milex.pagebuilder.'.$objectId.'.content');

            //set the lookup values
            $parent = $entity->getTranslationParent();
            if ($parent && isset($form['translationParent_lookup'])) {
                $form->get('translationParent_lookup')->setData($parent->getTitle());
            }

            // Set to view content
            $template = $entity->getTemplate();
            if (empty($template)) {
                $content = $entity->getCustomHtml();
                $form['customHtml']->setData($content);
            }
        }

        $slotTypes   = $model->getBuilderComponents($entity, 'slotTypes');
        $sections    = $model->getBuilderComponents($entity, 'sections');
        $sectionForm = $this->get('form.factory')->create(BuilderSectionType::class);

        return $this->delegateView([
            'viewParameters' => [
                'form'          => $this->setFormTheme($form, 'MilexPageBundle:Page:form.html.php', 'MilexPageBundle:FormTheme\Page'),
                'isVariant'     => $entity->isVariant(true),
                'tokens'        => $model->getBuilderComponents($entity, 'tokens'),
                'activePage'    => $entity,
                'themes'        => $this->factory->getInstalledThemes('page', true),
                'slots'         => $this->buildSlotForms($slotTypes),
                'sections'      => $this->buildSlotForms($sections),
                'builderAssets' => trim(preg_replace('/\s+/', ' ', $this->getAssetsForBuilder())), // strip new lines
                'sectionForm'   => $sectionForm->createView(),
                'previewUrl'    => $this->generateUrl('milex_page_preview', ['id' => $objectId], UrlGeneratorInterface::ABSOLUTE_URL),
                'permissions'   => $security->isGranted(
                    [
                        'page:preference_center:editown',
                        'page:preference_center:editother',
                    ],
                    'RETURN_ARRAY'
                ),
                'security'      => $security,
            ],
            'contentTemplate' => 'MilexPageBundle:Page:form.html.php',
            'passthroughVars' => [
                'activeLink'    => '#milex_page_index',
                'milexContent' => 'page',
                'route'         => $this->generateUrl('milex_page_action', [
                    'objectAction' => 'edit',
                    'objectId'     => $entity->getId(),
                ]),
                'validationError' => $this->getFormErrorForBuilder($form),
            ],
        ]);
    }

    /**
     * Clone an entity.
     *
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function cloneAction($objectId)
    {
        /** @var \Milex\PageBundle\Model\PageModel $model */
        $model  = $this->getModel('page.page');
        $entity = $model->getEntity($objectId);

        if (null != $entity) {
            if (!$this->get('milex.security')->isGranted('page:pages:create') ||
                !$this->get('milex.security')->hasEntityAccess(
                    'page:pages:viewown', 'page:pages:viewother', $entity->getCreatedBy()
                )
            ) {
                return $this->accessDenied();
            }

            $entity = clone $entity;
            $entity->setHits(0);
            $entity->setUniqueHits(0);
            $entity->setRevision(0);
            $entity->setVariantStartDate(null);
            $entity->setVariantHits(0);
            $entity->setIsPublished(false);

            $session     = $this->get('session');
            $contentName = 'milex.pagebuilder.'.$entity->getSessionId().'.content';

            $session->set($contentName, $entity->getCustomHtml());
        }

        return $this->newAction($entity);
    }

    /**
     * Deletes the entity.
     *
     * @param $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($objectId)
    {
        $page      = $this->get('session')->get('milex.page.page', 1);
        $returnUrl = $this->generateUrl('milex_page_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MilexPageBundle:Page:index',
            'passthroughVars' => [
                'activeLink'    => 'milex_page_index',
                'milexContent' => 'page',
            ],
        ];

        if ('POST' == $this->request->getMethod()) {
            /** @var \Milex\PageBundle\Model\PageModel $model */
            $model  = $this->getModel('page.page');
            $entity = $model->getEntity($objectId);

            if (null === $entity) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'milex.page.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('milex.security')->hasEntityAccess(
                'page:pages:deleteown',
                'page:pages:deleteother',
                $entity->getCreatedBy()
            )) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'page.page');
            }

            $model->deleteEntity($entity);

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'milex.core.notice.deleted',
                'msgVars' => [
                    '%name%' => $entity->getTitle(),
                    '%id%'   => $objectId,
                ],
            ];
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge($postActionVars, [
                'flashes' => $flashes,
            ])
        );
    }

    /**
     * Deletes a group of entities.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        $page      = $this->get('session')->get('milex.page.page', 1);
        $returnUrl = $this->generateUrl('milex_page_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MilexPageBundle:Page:index',
            'passthroughVars' => [
                'activeLink'    => 'milex_page_index',
                'milexContent' => 'page',
            ],
        ];

        if ('POST' == $this->request->getMethod()) {
            /** @var \Milex\PageBundle\Model\PageModel $model */
            $model     = $this->getModel('page');
            $ids       = json_decode($this->request->query->get('ids', '{}'));
            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if (null === $entity) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'milex.page.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('milex.security')->hasEntityAccess(
                    'page:pages:deleteown', 'page:pages:deleteother', $entity->getCreatedBy()
                )) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'page', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'milex.page.notice.batch_deleted',
                    'msgVars' => [
                        '%count%' => count($entities),
                    ],
                ];
            }
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge($postActionVars, [
                'flashes' => $flashes,
            ])
        );
    }

    /**
     * Activate the builder.
     *
     * @param int $objectId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function builderAction($objectId)
    {
        /** @var \Milex\PageBundle\Model\PageModel $model */
        $model = $this->getModel('page.page');

        //permission check
        if (false !== strpos($objectId, 'new')) {
            $isNew = true;
            if (!$this->get('milex.security')->isGranted('page:pages:create')) {
                return $this->accessDenied();
            }
            $entity = $model->getEntity();
            $entity->setSessionId($objectId);
        } else {
            $isNew  = false;
            $entity = $model->getEntity($objectId);
            if (null == $entity || !$this->get('milex.security')->hasEntityAccess(
                'page:pages:viewown', 'page:pages:viewother', $entity->getCreatedBy()
            )) {
                return $this->accessDenied();
            }
        }

        $template = InputHelper::clean($this->request->query->get('template'));
        if (empty($template)) {
            throw new \InvalidArgumentException('No template found');
        }
        $slots    = $this->factory->getTheme($template)->getSlots('page');

        //merge any existing changes
        $newContent = $this->get('session')->get('milex.pagebuilder.'.$objectId.'.content', []);
        $content    = $entity->getContent();

        if (is_array($newContent)) {
            $content = array_merge($content, $newContent);
            // Update the content for processSlots
            $entity->setContent($content);
        }

        $this->processSlots($slots, $entity);

        $logicalName = $this->factory->getHelper('theme')->checkForTwigTemplate(':'.$template.':page.html.php');

        return $this->render($logicalName, [
            'isNew'       => $isNew,
            'slots'       => $slots,
            'formFactory' => $this->get('form.factory'),
            'content'     => $content,
            'page'        => $entity,
            'template'    => $template,
            'basePath'    => $this->request->getBasePath(),
        ]);
    }

    /**
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function abtestAction($objectId)
    {
        /** @var \Milex\PageBundle\Model\PageModel $model */
        $model  = $this->getModel('page.page');
        $entity = $model->getEntity($objectId);

        if (!$entity) {
            return $this->notFound();
        }

        $parent = $entity->getVariantParent();

        if ($parent || !$this->get('milex.security')->isGranted('page:pages:create') ||
                !$this->get('milex.security')->hasEntityAccess(
                    'page:pages:viewown', 'page:pages:viewother', $entity->getCreatedBy()
                )
            ) {
            return $this->accessDenied();
        }

        $clone = clone $entity;

        //reset
        $clone->setHits(0);
        $clone->setRevision(0);
        $clone->setVariantHits(0);
        $clone->setUniqueHits(0);
        $clone->setVariantStartDate(null);
        $clone->setIsPublished(false);
        $clone->setVariantParent($entity);

        return $this->newAction($clone);
    }

    /**
     * Make the variant the main.
     *
     * @param $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function winnerAction($objectId)
    {
        //todo - add confirmation to button click
        $page      = $this->get('session')->get('milex.page.page', 1);
        $returnUrl = $this->generateUrl('milex_page_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MilexPageBundle:Page:index',
            'passthroughVars' => [
                'activeLink'    => 'milex_page_index',
                'milexContent' => 'page',
            ],
        ];

        if ('POST' == $this->request->getMethod()) {
            /** @var \Milex\PageBundle\Model\PageModel $model */
            $model  = $this->getModel('page.page');
            $entity = $model->getEntity($objectId);

            if (null === $entity) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'milex.page.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('milex.security')->hasEntityAccess(
                'page:pages:editown',
                'page:pages:editother',
                $entity->getCreatedBy()
            )) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'page.page');
            }

            $model->convertVariant($entity);

            $flashes[] = [
                'type'    => 'notice',
                'msg'     => 'milex.page.notice.activated',
                'msgVars' => [
                    '%name%' => $entity->getTitle(),
                    '%id%'   => $objectId,
                ],
            ];

            $postActionVars['viewParameters'] = [
                'objectAction' => 'view',
                'objectId'     => $objectId,
            ];
            $postActionVars['returnUrl']       = $this->generateUrl('milex_page_action', $postActionVars['viewParameters']);
            $postActionVars['contentTemplate'] = 'MilexPageBundle:Page:view';
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge($postActionVars, [
                'flashes' => $flashes,
            ])
        );
    }

    /**
     * PreProcess page slots for public view.
     *
     * @param array $slots
     * @param Page  $entity
     */
    private function processSlots($slots, $entity)
    {
        /** @var \Milex\CoreBundle\Templating\Helper\AssetsHelper $assetsHelper */
        $assetsHelper = $this->get('templating.helper.assets');
        /** @var \Milex\CoreBundle\Templating\Helper\SlotsHelper $slotsHelper */
        $slotsHelper = $this->get('templating.helper.slots');
        $formFactory = $this->get('form.factory');

        $slotsHelper->inBuilder(true);

        $content = $entity->getContent();

        foreach ($slots as $slot => $slotConfig) {
            // backward compatibility - if slotConfig array does not exist
            if (is_numeric($slot)) {
                $slot       = $slotConfig;
                $slotConfig = [];
            }

            // define default config if does not exist
            if (!isset($slotConfig['type'])) {
                $slotConfig['type'] = 'html';
            }

            if (!isset($slotConfig['placeholder'])) {
                $slotConfig['placeholder'] = 'milex.page.builder.addcontent';
            }

            $value = isset($content[$slot]) ? $content[$slot] : '';

            $slotsHelper->set($slot, "<div data-slot=\"text\" id=\"slot-{$slot}\">{$value}</div>");
        }

        $slotsHelper->start('builder'); ?>
<input type="hidden" id="builder_entity_id"
    value="<?php echo $entity->getSessionId(); ?>" />
<?php
        $slotsHelper->stop();
    }

    /**
     * Show submissions inside page.
     *
     * @param int $objectId
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function resultsAction($objectId, $page = 1)
    {
        /** @var \Milex\PageBundle\Model\PageModel $pageModel */
        $pageModel    = $this->getModel('page.page');
        $activePage   = $pageModel->getEntity($objectId);
        $session      = $this->get('session');
        $pageListPage = $session->get('milex.page.page', 1);
        $returnUrl    = $this->generateUrl('milex_page_index', ['page' => $pageListPage]);

        if (null === $activePage) {
            //redirect back to page list
            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $pageListPage],
                    'contentTemplate' => 'MilexPageBundle:Page:index',
                    'passthroughVars' => [
                        'activeLink'    => 'milex_page_index',
                        'milexContent' => 'page',
                    ],
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'milex.page.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ]
            );
        } elseif (!$this->get('milex.security')->hasEntityAccess(
            'page:pages:viewown',
            'page:pages:viewother',
            $activePage->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        }

        if ('POST' == $this->request->getMethod()) {
            $this->setListFilters($this->request->query->get('name'));
        }

        //set limits
        $limit = $session->get('milex.pageresult.'.$objectId.'.limit', $this->coreParametersHelper->get('default_pagelimit'));

        $page  = $page ?: 0;
        $start = ($page <= 1) ? 0 : (($page - 1) * $limit);

        // Set order direction to desc if not set
        if (!$session->get('milex.pageresult.'.$objectId.'.orderbydir', null)) {
            $session->set('milex.pageresult.'.$objectId.'.orderbydir', 'DESC');
        }

        $orderBy    = $session->get('milex.pageresult.'.$objectId.'.orderby', 's.date_submitted');
        $orderByDir = $session->get('milex.pageresult.'.$objectId.'.orderbydir', 'DESC');
        $filters    = $session->get('milex.pageresult.'.$objectId.'.filters', []);

        /** @var \Milex\FormBundle\Model\SubmissionModel $model */
        $model = $this->getModel('form.submission');

        if ($this->request->query->has('result')) {
            // Force ID
            $filters['s.id'] = ['column' => 's.id', 'expr' => 'like', 'value' => (int) $this->request->query->get('result'), 'strict' => false];
            $session->set("milex.pageresult.$objectId.filters", $filters);
        }
        //get the results
        $entities = $model->getEntitiesByPage(
            [
                'start'          => $start,
                'limit'          => $limit,
                'filter'         => ['force' => $filters],
                'orderBy'        => $orderBy,
                'orderByDir'     => $orderByDir,
                'withTotalCount' => true,
                'simpleResults'  => true,
                'activePage'     => $activePage,
            ]
        );

        $count   = $entities['count'];
        $results = $entities['results'];
        unset($entities);

        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            $lastPage = (1 === $count) ? 1 : (((ceil($count / $limit)) ?: 1) ?: 1);
            $session->set('milex.pageresult.page', $lastPage);
            $returnUrl = $this->generateUrl('milex_page_results', ['objectId' => $objectId, 'page' => $lastPage]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $lastPage],
                    'contentTemplate' => 'MilexPageBundle:Page:results',
                    'passthroughVars' => [
                        'activeLink'    => 'milex_page_index',
                        'milexContent' => 'pageresult',
                    ],
                ]
            );
        }

        //set what page currently on so that we can return here if need be
        $session->set('milex.pageresult.page', $page);

        $tmpl = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';

        return $this->delegateView(
            [
                'viewParameters' => [
                    'items'      => $results,
                    'filters'    => $filters,
                    'activePage' => $activePage,
                    'page'       => $page,
                    'totalCount' => $count,
                    'limit'      => $limit,
                    'tmpl'       => $tmpl,
                ],
                'contentTemplate' => 'MilexPageBundle:Result:list.html.php',
                'passthroughVars' => [
                    'activeLink'    => 'milex_page_index',
                    'milexContent' => 'pageresult',
                    'route'         => $this->generateUrl(
                        'milex_page_results',
                        [
                            'objectId' => $objectId,
                            'page'     => $page,
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Export submissions from a page.
     *
     * @param int    $objectId
     * @param string $format
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function exportAction($objectId, $format = 'csv')
    {
        $pageModel    = $this->getModel('page.page');
        $activePage   = $pageModel->getEntity($objectId);
        $session      = $this->get('session');
        $pageListPage = $session->get('milex.page.page', 1);
        $returnUrl    = $this->generateUrl('milex_page_index', ['page' => $pageListPage]);

        if (null === $activePage) {
            //redirect back to page list
            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $pageListPage],
                    'contentTemplate' => 'MilexPageBundle:Page:index',
                    'passthroughVars' => [
                        'activeLink'    => 'milex_page_index',
                        'milexContent' => 'page',
                    ],
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'milex.page.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ]
            );
        } elseif (!$this->get('milex.security')->hasEntityAccess(
            'page:pages:viewown',
            'page:pages:viewother',
            $activePage->getCreatedBy()
        )
        ) {
            return $this->accessDenied();
        }

        $orderBy    = $session->get('milex.pageresult.'.$objectId.'.orderby', 's.date_submitted');
        $orderByDir = $session->get('milex.pageresult.'.$objectId.'.orderbydir', 'DESC');
        $filters    = $session->get('milex.pageresult.'.$objectId.'.filters', []);

        $args = [
            'limit'      => false,
            'filter'     => ['force' => $filters],
            'orderBy'    => $orderBy,
            'orderByDir' => $orderByDir,
            'activePage' => $activePage,
        ];

        /** @var \Milex\FormBundle\Model\SubmissionModel $model */
        $model = $this->getModel('form.submission');

        return $model->exportResultsForPage($format, $activePage, $args);
    }

    public function getModelName(): string
    {
        return 'page';
    }

    protected function getDefaultOrderDirection()
    {
        return 'DESC';
    }
}
