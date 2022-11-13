<?php

namespace Milex\CategoryBundle\Controller;

use Milex\CategoryBundle\CategoryEvents;
use Milex\CategoryBundle\Event\CategoryTypesEvent;
use Milex\CategoryBundle\Model\CategoryModel;
use Milex\CoreBundle\Controller\AbstractFormController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CategoryController extends AbstractFormController
{
    /**
     * @param        $bundle
     * @param        $objectAction
     * @param int    $objectId
     * @param string $objectModel
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function executeCategoryAction($bundle, $objectAction, $objectId = 0, $objectModel = '')
    {
        if (method_exists($this, "{$objectAction}Action")) {
            return $this->{"{$objectAction}Action"}($bundle, $objectId, $objectModel);
        } else {
            return $this->accessDenied();
        }
    }

    /**
     * @param     $bundle
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($bundle, $page = 1)
    {
        $session = $this->get('session');

        $search = $this->request->query->get('search', $session->get('milex.category.filter', ''));
        $bundle = $this->request->query->get('bundle', $session->get('milex.category.type', $bundle));

        if ($bundle) {
            $session->set('milex.category.type', $bundle);
        }

        // hack to make pagination work for default list view
        if ('all' == $bundle) {
            $bundle = 'category';
        }

        $session->set('milex.category.filter', $search);

        //set some permissions
        $permissionBase = $this->getModel('category')->getPermissionBase($bundle);
        $permissions    = $this->get('milex.security')->isGranted(
            [
                $permissionBase.':view',
                $permissionBase.':create',
                $permissionBase.':edit',
                $permissionBase.':delete',
            ],
            'RETURN_ARRAY'
        );

        if (!$permissions[$permissionBase.':view']) {
            return $this->accessDenied();
        }

        $this->setListFilters();

        $viewParams = [
            'page'   => $page,
            'bundle' => $bundle,
        ];

        //set limits
        $limit = $session->get('milex.category.limit', $this->coreParametersHelper->get('default_pagelimit'));
        $start = (1 === $page) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $filter = ['string' => $search];

        if ('category' != $bundle) {
            $filter['force'] = [
                [
                    'column' => 'c.bundle',
                    'expr'   => 'eq',
                    'value'  => $bundle,
                ],
            ];
        }

        $orderBy    = $this->get('session')->get('milex.category.orderby', 'c.title');
        $orderByDir = $this->get('session')->get('milex.category.orderbydir', 'DESC');

        $entities = $this->getModel('category')->getEntities(
            [
                'start'      => $start,
                'limit'      => $limit,
                'filter'     => $filter,
                'orderBy'    => $orderBy,
                'orderByDir' => $orderByDir,
            ]
        );

        $count = count($entities);
        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            if (1 === $count) {
                $lastPage = 1;
            } else {
                $lastPage = (ceil($count / $limit)) ?: 1;
            }
            $viewParams['page'] = $lastPage;
            $session->set('milex.category.page', $lastPage);
            $returnUrl = $this->generateUrl('milex_category_index', $viewParams);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $lastPage],
                    'contentTemplate' => 'MilexCategoryBundle:Category:index',
                    'passthroughVars' => [
                        'activeLink'    => '#milex_'.$bundle.'category_index',
                        'milexContent' => 'category',
                    ],
                ]
            );
        }

        $categoryTypes = ['category' => $this->get('translator')->trans('milex.core.select')];

        $dispatcher = $this->dispatcher;
        if ($dispatcher->hasListeners(CategoryEvents::CATEGORY_ON_BUNDLE_LIST_BUILD)) {
            $event = new CategoryTypesEvent();
            $dispatcher->dispatch(CategoryEvents::CATEGORY_ON_BUNDLE_LIST_BUILD, $event);
            $categoryTypes = array_merge($categoryTypes, $event->getCategoryTypes());
        }

        //set what page currently on so that we can return here after form submission/cancellation
        $session->set('milex.category.page', $page);

        $tmpl = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';

        return $this->delegateView(
            [
                'returnUrl'      => $this->generateUrl('milex_category_index', $viewParams),
                'viewParameters' => [
                    'bundle'         => $bundle,
                    'permissionBase' => $permissionBase,
                    'searchValue'    => $search,
                    'items'          => $entities,
                    'page'           => $page,
                    'limit'          => $limit,
                    'permissions'    => $permissions,
                    'tmpl'           => $tmpl,
                    'categoryTypes'  => $categoryTypes,
                ],
                'contentTemplate' => 'MilexCategoryBundle:Category:list.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#milex_'.$bundle.'category_index',
                    'milexContent' => 'category',
                    'route'         => $this->generateUrl('milex_category_index', $viewParams),
                ],
            ]
        );
    }

    /**
     * Generates new form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction($bundle)
    {
        $session    = $this->get('session');
        $model      = $this->getModel('category');
        $entity     = $model->getEntity();
        $success    = $closeModal    = 0;
        $cancelled  = $valid  = false;
        $method     = $this->request->getMethod();
        $inForm     = $this->getInFormValue($method);
        $showSelect = $this->request->get('show_bundle_select', false);

        //not found
        if (!$this->get('milex.security')->isGranted($model->getPermissionBase($bundle).':create')) {
            return $this->modalAccessDenied();
        }
        //Create the form
        $action = $this->generateUrl('milex_category_action', [
            'objectAction' => 'new',
            'bundle'       => $bundle,
        ]);
        $form = $model->createForm($entity, $this->get('form.factory'), $action, ['bundle' => $bundle, 'show_bundle_select' => $showSelect]);
        $form['inForm']->setData($inForm);
        ///Check for a submitted form and process it
        if ('POST' == $method) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $success = 1;

                    //form is valid so process the data
                    $model->saveEntity($entity, $form->get('buttons')->get('save')->isClicked());

                    $this->addFlash('milex.category.notice.created', [
                        '%name%' => $entity->getName(),
                    ]);
                }
            } else {
                $success = 1;
            }
        }

        $closeModal = ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked()));

        if ($closeModal) {
            if ($inForm) {
                return new JsonResponse([
                    'milexContent' => 'category',
                    'closeModal'    => 1,
                    'inForm'        => 1,
                    'categoryName'  => $entity->getName(),
                    'categoryId'    => $entity->getId(),
                ]);
            }

            $viewParameters = [
                'page'   => $session->get('milex.category.page'),
                'bundle' => $bundle,
            ];

            return $this->postActionRedirect([
                'returnUrl'       => $this->generateUrl('milex_category_index', $viewParameters),
                'viewParameters'  => $viewParameters,
                'contentTemplate' => 'MilexCategoryBundle:Category:index',
                'passthroughVars' => [
                    'activeLink'    => '#milex_'.$bundle.'category_index',
                    'milexContent' => 'category',
                    'closeModal'    => 1,
                ],
            ]);
        } elseif (!empty($valid)) {
            //return edit view to prevent duplicates
            return $this->editAction($bundle, $entity->getId(), true);
        } else {
            return $this->ajaxAction([
                'contentTemplate' => 'MilexCategoryBundle:Category:form.html.php',
                'viewParameters'  => [
                    'form'           => $form->createView(),
                    'activeCategory' => $entity,
                    'bundle'         => $bundle,
                ],
                'passthroughVars' => [
                    'milexContent' => 'category',
                    'success'       => $success,
                    'route'         => false,
                ],
            ]);
        }
    }

    /**
     * Generates edit form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($bundle, $objectId, $ignorePost = false)
    {
        $session = $this->get('session');
        /** @var CategoryModel $model */
        $model     = $this->getModel('category');
        $entity    = $model->getEntity($objectId);
        $success   = $closeModal   = 0;
        $cancelled = $valid = false;
        $method    = $this->request->getMethod();
        $inForm    = $this->getInFormValue($method);
        //not found
        if (null === $entity) {
            $closeModal = true;
        } elseif (!$this->get('milex.security')->isGranted($model->getPermissionBase($bundle).':view')) {
            return $this->modalAccessDenied();
        } elseif ($model->isLocked($entity)) {
            return $this->modalAccessDenied();
        }

        //Create the form
        $action = $this->generateUrl(
            'milex_category_action',
            [
                'objectAction' => 'edit',
                'objectId'     => $objectId,
                'bundle'       => $bundle,
            ]
        );
        $form = $model->createForm($entity, $this->get('form.factory'), $action, ['bundle' => $bundle]);
        $form['inForm']->setData($inForm);

        ///Check for a submitted form and process it
        if (!$ignorePost && 'POST' == $method) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $success = 1;

                    //form is valid so process the data
                    $model->saveEntity($entity, $form->get('buttons')->get('save')->isClicked());

                    $this->addFlash(
                        'milex.category.notice.updated',
                        [
                            '%name%' => $entity->getTitle(),
                        ]
                    );

                    if ($form->get('buttons')->get('apply')->isClicked()) {
                        // Rebuild the form with new action so that apply doesn't keep creating a clone
                        $action = $this->generateUrl(
                            'milex_category_action',
                            [
                                'objectAction' => 'edit',
                                'objectId'     => $entity->getId(),
                                'bundle'       => $bundle,
                            ]
                        );
                        $form = $model->createForm($entity, $this->get('form.factory'), $action, ['bundle' => $bundle]);
                    }
                }
            } else {
                $success = 1;

                //unlock the entity
                $model->unlockEntity($entity);
            }
        } else {
            //lock the entity
            $model->lockEntity($entity);
        }

        $closeModal = ($closeModal || $cancelled || ($valid && $form->get('buttons')->get('save')->isClicked()));

        if ($closeModal) {
            if ($inForm) {
                return new JsonResponse(
                    [
                        'milexContent' => 'category',
                        'closeModal'    => 1,
                        'inForm'        => 1,
                        'categoryName'  => $entity->getTitle(),
                        'categoryId'    => $entity->getId(),
                    ]
                );
            }

            $viewParameters = [
                'page'   => $session->get('milex.category.page'),
                'bundle' => $bundle,
            ];

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $this->generateUrl('milex_category_index', $viewParameters),
                    'viewParameters'  => $viewParameters,
                    'contentTemplate' => 'MilexCategoryBundle:Category:index',
                    'passthroughVars' => [
                        'activeLink'    => '#milex_'.$bundle.'category_index',
                        'milexContent' => 'category',
                        'closeModal'    => 1,
                    ],
                ]
            );
        } else {
            return $this->ajaxAction(
                [
                    'contentTemplate' => 'MilexCategoryBundle:Category:form.html.php',
                    'viewParameters'  => [
                        'form'           => $form->createView(),
                        'activeCategory' => $entity,
                        'bundle'         => $bundle,
                    ],
                    'passthroughVars' => [
                        'milexContent' => 'category',
                        'success'       => $success,
                        'route'         => false,
                    ],
                ]
            );
        }
    }

    /**
     * Deletes the entity.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($bundle, $objectId)
    {
        $session    = $this->get('session');
        $page       = $session->get('milex.category.page', 1);
        $viewParams = [
            'page'   => $page,
            'bundle' => $bundle,
        ];
        $returnUrl = $this->generateUrl('milex_category_index', $viewParams);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => $viewParams,
            'contentTemplate' => 'MilexCategoryBundle:Category:index',
            'passthroughVars' => [
                'activeLink'    => 'milex_'.$bundle.'category_index',
                'milexContent' => 'category',
            ],
        ];

        if ('POST' == $this->request->getMethod()) {
            $model  = $this->getModel('category');
            $entity = $model->getEntity($objectId);

            if (null === $entity) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'milex.category.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('milex.security')->isGranted($model->getPermissionBase($bundle).':delete')) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'category.category');
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
     * @param string $bundle
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction($bundle)
    {
        $session    = $this->get('session');
        $page       = $session->get('milex.category.page', 1);
        $viewParams = [
            'page'   => $page,
            'bundle' => $bundle,
        ];
        $returnUrl = $this->generateUrl('milex_category_index', $viewParams);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => $viewParams,
            'contentTemplate' => 'MilexCategoryBundle:Category:index',
            'passthroughVars' => [
                'activeLink'    => 'milex_'.$bundle.'category_index',
                'milexContent' => 'category',
            ],
        ];

        if ('POST' == $this->request->getMethod()) {
            $model     = $this->getModel('category');
            $ids       = json_decode($this->request->query->get('ids', '{}'));
            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if (null === $entity) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'milex.category.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('milex.security')->isGranted($model->getPermissionBase($bundle).':delete')) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'category', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'milex.category.notice.batch_deleted',
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

    private function getInFormValue(string $method): int
    {
        $inForm = $this->request->get('inForm', 0);
        if (Request::METHOD_POST == $method) {
            $category_form = $this->request->request->get('category_form');
            $inForm        = $category_form['inForm'] ?? 0;
        }

        return (int) $inForm;
    }
}
