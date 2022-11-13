<?php

namespace Milex\LeadBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Milex\CoreBundle\Controller\FormController;
use Milex\CoreBundle\Helper\EmojiHelper;
use Milex\CoreBundle\Model\IteratorExportDataModel;
use Milex\LeadBundle\DataObject\LeadManipulator;
use Milex\LeadBundle\Deduplicate\ContactMerger;
use Milex\LeadBundle\Deduplicate\Exception\SameContactException;
use Milex\LeadBundle\Entity\DoNotContact;
use Milex\LeadBundle\Entity\DoNotContactRepository;
use Milex\LeadBundle\Entity\Lead;
use Milex\LeadBundle\Form\Type\BatchType;
use Milex\LeadBundle\Form\Type\DncType;
use Milex\LeadBundle\Form\Type\EmailType;
use Milex\LeadBundle\Form\Type\MergeType;
use Milex\LeadBundle\Form\Type\OwnerType;
use Milex\LeadBundle\Form\Type\StageType;
use Milex\LeadBundle\Model\LeadModel;
use Milex\LeadBundle\Model\ListModel;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class LeadController extends FormController
{
    use LeadDetailsTrait;
    use FrequencyRuleTrait;

    /**
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page = 1)
    {
        //set some permissions
        $permissions = $this->get('milex.security')->isGranted(
            [
                'lead:leads:viewown',
                'lead:leads:viewother',
                'lead:leads:create',
                'lead:leads:editown',
                'lead:leads:editother',
                'lead:leads:deleteown',
                'lead:leads:deleteother',
                'lead:imports:view',
                'lead:imports:create',
            ],
            'RETURN_ARRAY'
        );

        if (!$permissions['lead:leads:viewown'] && !$permissions['lead:leads:viewother']) {
            return $this->accessDenied();
        }

        $this->setListFilters();

        /** @var \Milex\LeadBundle\Model\LeadModel $model */
        $model   = $this->getModel('lead');
        $session = $this->get('session');
        //set limits
        $limit = $session->get('milex.lead.limit', $this->get('milex.helper.core_parameters')->get('default_pagelimit'));
        $start = (1 === $page) ? 0 : (($page - 1) * $limit);
        if ($start < 0) {
            $start = 0;
        }

        $search = $this->request->get('search', $session->get('milex.lead.filter', ''));
        $session->set('milex.lead.filter', $search);

        //do some default filtering
        $orderBy    = $session->get('milex.lead.orderby', 'l.last_active');
        // Add an id field to orderBy. Prevent Null-value ordering
        $orderById  = 'l.id' !== $orderBy ? ', l.id' : '';
        $orderBy    = $orderBy.$orderById;
        $orderByDir = $session->get('milex.lead.orderbydir', 'DESC');

        $filter      = ['string' => $search, 'force' => ''];
        $translator  = $this->get('translator');
        $anonymous   = $translator->trans('milex.lead.lead.searchcommand.isanonymous');
        $listCommand = $translator->trans('milex.lead.lead.searchcommand.list');
        $mine        = $translator->trans('milex.core.searchcommand.ismine');
        $indexMode   = $this->request->get('view', $session->get('milex.lead.indexmode', 'list'));

        $session->set('milex.lead.indexmode', $indexMode);

        $anonymousShowing = false;
        if ('list' != $indexMode || ('list' == $indexMode && false === strpos($search, $anonymous))) {
            //remove anonymous leads unless requested to prevent clutter
            $filter['force'] .= " !$anonymous";
        } elseif (false !== strpos($search, $anonymous) && false === strpos($search, '!'.$anonymous)) {
            $anonymousShowing = true;
        }

        if (!$permissions['lead:leads:viewother']) {
            $filter['force'] .= " $mine";
        }

        $results = $model->getEntities([
            'start'          => $start,
            'limit'          => $limit,
            'filter'         => $filter,
            'orderBy'        => $orderBy,
            'orderByDir'     => $orderByDir,
            'withTotalCount' => true,
        ]);

        $count = $results['count'];
        unset($results['count']);

        $leads = $results['results'];
        unset($results);

        if ($count && $count < ($start + 1)) {
            //the number of entities are now less then the current page so redirect to the last page
            if (1 === $count) {
                $lastPage = 1;
            } else {
                $lastPage = (ceil($count / $limit)) ?: 1;
            }
            $session->set('milex.lead.page', $lastPage);
            $returnUrl = $this->generateUrl('milex_contact_index', ['page' => $lastPage]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $lastPage],
                    'contentTemplate' => 'MilexLeadBundle:Lead:index',
                    'passthroughVars' => [
                        'activeLink'    => '#milex_contact_index',
                        'milexContent' => 'lead',
                    ],
                ]
            );
        }

        //set what page currently on so that we can return here after form submission/cancellation
        $session->set('milex.lead.page', $page);

        $tmpl = $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index';

        $listArgs = [];
        if (!$this->get('milex.security')->isGranted('lead:lists:viewother')) {
            $listArgs['filter']['force'] = " $mine";
        }

        $lists = $this->getModel('lead.list')->getUserLists();

        //check to see if in a single list
        $inSingleList = (1 === substr_count($search, "$listCommand:")) ? true : false;
        $list         = [];
        if ($inSingleList) {
            preg_match("/$listCommand:(.*?)(?=\s|$)/", $search, $matches);

            if (!empty($matches[1])) {
                $alias = $matches[1];
                foreach ($lists as $l) {
                    if ($alias === $l['alias']) {
                        $list = $l;
                        break;
                    }
                }
            }
        }

        // Get the max ID of the latest lead added
        $maxLeadId = $model->getRepository()->getMaxLeadId();

        /** @var DoNotContactRepository $dncRepository */
        $dncRepository = $this->getModel('lead.dnc')->getDncRepo();

        return $this->delegateView(
            [
                'viewParameters' => [
                    'searchValue'      => $search,
                    'columns'          => $this->get('milex.lead.columns.dictionary')->getColumns(),
                    'items'            => $leads,
                    'page'             => $page,
                    'totalItems'       => $count,
                    'limit'            => $limit,
                    'permissions'      => $permissions,
                    'tmpl'             => $tmpl,
                    'indexMode'        => $indexMode,
                    'lists'            => $lists,
                    'currentList'      => $list,
                    'security'         => $this->get('milex.security'),
                    'inSingleList'     => $inSingleList,
                    'noContactList'    => $dncRepository->getChannelList(null, array_keys($leads)),
                    'maxLeadId'        => $maxLeadId,
                    'anonymousShowing' => $anonymousShowing,
                ],
                'contentTemplate' => "MilexLeadBundle:Lead:{$indexMode}.html.php",
                'passthroughVars' => [
                    'activeLink'    => '#milex_contact_index',
                    'milexContent' => 'lead',
                    'route'         => $this->generateUrl('milex_contact_index', ['page' => $page]),
                ],
            ]
        );
    }

    /**
     * @return JsonResponse|Response
     */
    public function quickAddAction()
    {
        /** @var \Milex\LeadBundle\Model\LeadModel $model */
        $model = $this->getModel('lead.lead');

        // Get the quick add form
        $action = $this->generateUrl('milex_contact_action', ['objectAction' => 'new', 'qf' => 1]);

        $fields = $this->getModel('lead.field')->getEntities(
            [
                'filter' => [
                    'force' => [
                        [
                            'column' => 'f.isPublished',
                            'expr'   => 'eq',
                            'value'  => true,
                        ],
                        [
                            'column' => 'f.isShortVisible',
                            'expr'   => 'eq',
                            'value'  => true,
                        ],
                        [
                            'column' => 'f.object',
                            'expr'   => 'like',
                            'value'  => 'lead',
                        ],
                    ],
                ],
                'hydration_mode' => 'HYDRATE_ARRAY',
            ]
        );

        $quickForm = $model->createForm($model->getEntity(), $this->get('form.factory'), $action, ['fields' => $fields, 'isShortForm' => true]);

        //set the default owner to the currently logged in user
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        $quickForm->get('owner')->setData($currentUser);

        return $this->delegateView(
            [
                'viewParameters' => [
                    'quickForm' => $quickForm->createView(),
                ],
                'contentTemplate' => 'MilexLeadBundle:Lead:quickadd.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#milex_contact_index',
                    'milexContent' => 'lead',
                    'route'         => false,
                ],
            ]
        );
    }

    /**
     * Loads a specific lead into the detailed panel.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($objectId)
    {
        /** @var \Milex\LeadBundle\Model\LeadModel $model */
        $model = $this->getModel('lead.lead');

        // When we change company data these changes get cached
        // so we need to clear the entity manager
        $model->getRepository()->clear();

        /** @var \Milex\LeadBundle\Entity\Lead $lead */
        $lead = $model->getEntity($objectId);

        //set some permissions
        $permissions = $this->get('milex.security')->isGranted(
            [
                'lead:leads:viewown',
                'lead:leads:viewother',
                'lead:leads:create',
                'lead:leads:editown',
                'lead:leads:editother',
                'lead:leads:deleteown',
                'lead:leads:deleteother',
            ],
            'RETURN_ARRAY'
        );

        if (null === $lead) {
            //get the page we came from
            $page = $this->get('session')->get('milex.lead.page', 1);

            //set the return URL
            $returnUrl = $this->generateUrl('milex_contact_index', ['page' => $page]);

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $returnUrl,
                    'viewParameters'  => ['page' => $page],
                    'contentTemplate' => 'MilexLeadBundle:Lead:index',
                    'passthroughVars' => [
                        'activeLink'    => '#milex_contact_index',
                        'milexContent' => 'contact',
                    ],
                    'flashes' => [
                        [
                            'type'    => 'error',
                            'msg'     => 'milex.lead.lead.error.notfound',
                            'msgVars' => ['%id%' => $objectId],
                        ],
                    ],
                ]
            );
        }

        if (!$this->get('milex.security')->hasEntityAccess(
            'lead:leads:viewown',
            'lead:leads:viewother',
            $lead->getPermissionUser()
        )
        ) {
            return $this->accessDenied();
        }

        $fields            = $lead->getFields();
        $integrationHelper = $this->get('milex.helper.integration');
        $socialProfiles    = (array) $integrationHelper->getUserProfiles($lead, $fields);
        $socialProfileUrls = $integrationHelper->getSocialProfileUrlRegex(false);
        /* @var \Milex\LeadBundle\Model\CompanyModel $model */

        $companyModel = $this->getModel('lead.company');

        $companiesRepo = $companyModel->getRepository();
        $companies     = $companiesRepo->getCompaniesByLeadId($objectId);
        // Set the social profile templates
        if ($socialProfiles) {
            foreach ($socialProfiles as $integration => &$details) {
                if ($integrationObject = $integrationHelper->getIntegrationObject($integration)) {
                    if ($template = $integrationObject->getSocialProfileTemplate()) {
                        $details['social_profile_template'] = $template;
                    }
                }

                if (!isset($details['social_profile_template'])) {
                    // No profile template found
                    unset($socialProfiles[$integration]);
                }
            }
        }

        // We need the DoNotContact repository to check if a lead is flagged as do not contact
        $dnc = $this->getDoctrine()->getManager()->getRepository('MilexLeadBundle:DoNotContact')->getEntriesByLeadAndChannel($lead, 'email');

        $dncSms = $this->getDoctrine()->getManager()->getRepository('MilexLeadBundle:DoNotContact')->getEntriesByLeadAndChannel($lead, 'sms');

        $integrationRepo = $this->get('doctrine.orm.entity_manager')->getRepository('MilexPluginBundle:IntegrationEntity');

        /** @var ListModel */
        $model = $this->getModel('lead.list');
        $lists = $model->getRepository()->getLeadLists([$lead], true, true);

        return $this->delegateView(
            [
                'viewParameters' => [
                    'lead'              => $lead,
                    'avatarPanelState'  => $this->request->cookies->get('milex_lead_avatar_panel', 'expanded'),
                    'fields'            => $fields,
                    'companies'         => $companies,
                    'lists'             => $lists,
                    'socialProfiles'    => $socialProfiles,
                    'socialProfileUrls' => $socialProfileUrls,
                    'places'            => $this->getPlaces($lead),
                    'permissions'       => $permissions,
                    'events'            => $this->getEngagements($lead),
                    'upcomingEvents'    => $this->getScheduledCampaignEvents($lead),
                    'engagementData'    => $this->getEngagementData($lead),
                    'noteCount'         => $this->getModel('lead.note')->getNoteCount($lead, true),
                    'integrations'      => $integrationRepo->getIntegrationEntityByLead($lead->getId()),
                    'devices'           => $this->get('milex.lead.repository.lead_device')->getLeadDevices($lead),
                    'auditlog'          => $this->getAuditlogs($lead),
                    'doNotContact'      => end($dnc),
                    'doNotContactSms'   => end($dncSms),
                    'leadNotes'         => $this->forward(
                        'MilexLeadBundle:Note:index',
                        [
                            'leadId'     => $lead->getId(),
                            'ignoreAjax' => 1,
                        ]
                    )->getContent(),
                ],
                'contentTemplate' => 'MilexLeadBundle:Lead:lead.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#milex_contact_index',
                    'milexContent' => 'lead',
                    'route'         => $this->generateUrl(
                        'milex_contact_action',
                        [
                            'objectAction' => 'view',
                            'objectId'     => $lead->getId(),
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Generates new form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction()
    {
        /** @var LeadModel $model */
        $model = $this->getModel('lead.lead');
        $lead  = $model->getEntity();

        if (!$this->get('milex.security')->isGranted('lead:leads:create')) {
            return $this->accessDenied();
        }

        //set the page we came from
        $page   = $this->get('session')->get('milex.lead.page', 1);
        $action = $this->generateUrl('milex_contact_action', ['objectAction' => 'new']);
        $fields = $this->getModel('lead.field')->getPublishedFieldArrays('lead');
        $form   = $model->createForm($lead, $this->get('form.factory'), $action, ['fields' => $fields]);

        ///Check for a submitted form and process it
        if ('POST' === $this->request->getMethod()) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    //get custom field values
                    $data = $this->request->request->get('lead');

                    //pull the data from the form in order to apply the form's formatting
                    foreach ($form as $f) {
                        if ('companies' !== $f->getName()) {
                            $data[$f->getName()] = $f->getData();
                        }
                    }

                    $companies = [];
                    if (isset($data['companies'])) {
                        $companies = $data['companies'];
                        unset($data['companies']);
                    }

                    $model->setFieldValues($lead, $data, true);

                    //form is valid so process the data
                    $lead->setManipulator(new LeadManipulator(
                        'lead',
                        'lead',
                        null,
                        $this->get('milex.helper.user')->getUser()->getName()
                    ));

                    /** @var LeadRepository $contactRepository */
                    $contactRepository = $this->getDoctrine()->getManager()->getRepository(Lead::class);

                    // Save here as we need the entity with an ID for the company code bellow.
                    $contactRepository->saveEntity($lead);

                    if (!empty($companies)) {
                        $model->modifyCompanies($lead, $companies);
                    }

                    // Save here through the model to trigger all subscribers.
                    $model->saveEntity($lead);

                    // Upload avatar if applicable
                    $image = $form['preferred_profile_image']->getData();
                    if ('custom' === $image) {
                        // Check for a file
                        if ($form['custom_avatar']->getData()) {
                            $this->uploadAvatar($lead);
                        }
                    }

                    $identifier = $this->get('translator')->trans($lead->getPrimaryIdentifier());

                    $this->addFlash(
                        'milex.core.notice.created',
                        [
                            '%name%'      => $identifier,
                            '%menu_link%' => 'milex_contact_index',
                            '%url%'       => $this->generateUrl(
                                'milex_contact_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $lead->getId(),
                                ]
                            ),
                        ]
                    );

                    $inQuickForm = $this->request->get('qf', false);

                    if ($inQuickForm) {
                        $viewParameters = ['page' => $page];
                        $returnUrl      = $this->generateUrl('milex_contact_index', $viewParameters);
                        $template       = 'MilexLeadBundle:Lead:index';
                    } elseif ($form->get('buttons')->get('save')->isClicked()) {
                        $viewParameters = [
                            'objectAction' => 'view',
                            'objectId'     => $lead->getId(),
                        ];
                        $returnUrl = $this->generateUrl('milex_contact_action', $viewParameters);
                        $template  = 'MilexLeadBundle:Lead:view';
                    } else {
                        return $this->editAction($lead->getId(), true);
                    }
                } else {
                    $formErrors = $this->getFormErrorMessages($form);
                    $this->addFlash(
                        $this->getFormErrorMessage($formErrors),
                        [],
                        'error'
                    );
                }
            } else {
                $viewParameters = ['page' => $page];
                $returnUrl      = $this->generateUrl('milex_contact_index', $viewParameters);
                $template       = 'MilexLeadBundle:Lead:index';
            }

            if ($cancelled || $valid) { //cancelled or success
                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $returnUrl,
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $template,
                        'passthroughVars' => [
                            'activeLink'    => '#milex_contact_index',
                            'milexContent' => 'lead',
                            'closeModal'    => 1, //just in case in quick form
                        ],
                    ]
                );
            }
        } else {
            //set the default owner to the currently logged in user
            $currentUser = $this->get('security.token_storage')->getToken()->getUser();
            $form->get('owner')->setData($currentUser);
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'   => $form->createView(),
                    'lead'   => $lead,
                    'fields' => $model->organizeFieldsByGroup($fields),
                ],
                'contentTemplate' => 'MilexLeadBundle:Lead:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#milex_contact_index',
                    'milexContent' => 'lead',
                    'route'         => $this->generateUrl(
                        'milex_contact_action',
                        [
                            'objectAction' => 'new',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Generates edit form.
     *
     * @param            $objectId
     * @param bool|false $ignorePost
     *
     * @return array|JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($objectId, $ignorePost = false)
    {
        /** @var LeadModel $model */
        $model = $this->getModel('lead.lead');
        $lead  = $model->getEntity($objectId);

        //set the page we came from
        $page = $this->get('session')->get('milex.lead.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('milex_contact_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MilexLeadBundle:Lead:index',
            'passthroughVars' => [
                'activeLink'    => '#milex_contact_index',
                'milexContent' => 'lead',
            ],
        ];
        //lead not found
        if (null === $lead) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'milex.lead.lead.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        } elseif (!$this->get('milex.security')->hasEntityAccess(
            'lead:leads:editown',
            'lead:leads:editother',
            $lead->getPermissionUser()
        )
        ) {
            return $this->accessDenied();
        } elseif ($model->isLocked($lead)) {
            //deny access if the entity is locked
            return $this->isLocked($postActionVars, $lead, 'lead.lead');
        }

        $action = $this->generateUrl('milex_contact_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $fields = $this->getModel('lead.field')->getPublishedFieldArrays('lead');
        $form   = $model->createForm($lead, $this->get('form.factory'), $action, ['fields' => $fields]);

        ///Check for a submitted form and process it
        if (!$ignorePost && 'POST' == $this->request->getMethod()) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $data = $this->request->request->get('lead');

                    //pull the data from the form in order to apply the form's formatting
                    foreach ($form as $f) {
                        if (('companies' !== $f->getName()) && ('company' !== $f->getName())) {
                            $data[$f->getName()] = $f->getData();
                        }
                    }

                    $companies = [];
                    if (isset($data['companies'])) {
                        $companies = $data['companies'];
                        unset($data['companies']);
                    }
                    $model->setFieldValues($lead, $data, true);

                    //form is valid so process the data
                    $lead->setManipulator(new LeadManipulator(
                        'lead',
                        'lead',
                        $objectId,
                        $this->get('milex.helper.user')->getUser()->getName()
                    ));
                    $model->modifyCompanies($lead, $companies);
                    $model->saveEntity($lead, $form->get('buttons')->get('save')->isClicked());

                    // Upload avatar if applicable
                    $image = $form['preferred_profile_image']->getData();
                    if ('custom' == $image) {
                        // Check for a file
                        /** @var UploadedFile $file */
                        if ($file = $form['custom_avatar']->getData()) {
                            $this->uploadAvatar($lead);

                            // Note the avatar update so that it can be forced to update
                            $this->get('session')->set('milex.lead.avatar.updated', true);
                        }
                    }

                    $identifier = $this->get('translator')->trans($lead->getPrimaryIdentifier());

                    $this->addFlash(
                        'milex.core.notice.updated',
                        [
                            '%name%'      => $identifier,
                            '%menu_link%' => 'milex_contact_index',
                            '%url%'       => $this->generateUrl(
                                'milex_contact_action',
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $lead->getId(),
                                ]
                            ),
                        ]
                    );
                } else {
                    $formErrors = $this->getFormErrorMessages($form);
                    $this->addFlash(
                        $this->getFormErrorMessage($formErrors),
                        [],
                        'error'
                    );
                }
            } else {
                //unlock the entity
                $model->unlockEntity($lead);
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                $viewParameters = [
                    'objectAction' => 'view',
                    'objectId'     => $lead->getId(),
                ];

                return $this->postActionRedirect(
                    array_merge(
                        $postActionVars,
                        [
                            'returnUrl'       => $this->generateUrl('milex_contact_action', $viewParameters),
                            'viewParameters'  => $viewParameters,
                            'contentTemplate' => 'MilexLeadBundle:Lead:view',
                        ]
                    )
                );
            } elseif ($valid) {
                // Refetch and recreate the form in order to populate data manipulated in the entity itself
                $lead = $model->getEntity($objectId);
                $form = $model->createForm($lead, $this->get('form.factory'), $action, ['fields' => $fields]);
            }
        } else {
            //lock the entity
            $model->lockEntity($lead);
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'form'   => $form->createView(),
                    'lead'   => $lead,
                    'fields' => $lead->getFields(), //pass in the lead fields as they are already organized by ['group']['alias']
                ],
                'contentTemplate' => 'MilexLeadBundle:Lead:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#milex_contact_index',
                    'milexContent' => 'lead',
                    'route'         => $this->generateUrl(
                        'milex_contact_action',
                        [
                            'objectAction' => 'edit',
                            'objectId'     => $lead->getId(),
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Upload an asset.
     */
    private function uploadAvatar(Lead $lead)
    {
        $leadInformation = $this->request->files->get('lead', []);
        $file            = $leadInformation['custom_avatar'] ?? null;
        $avatarDir       = $this->get('milex.helper.template.avatar')->getAvatarPath(true);

        if (!file_exists($avatarDir)) {
            mkdir($avatarDir);
        }

        $file->move($avatarDir, 'avatar'.$lead->getId());

        //remove the file from request
        $this->request->files->remove('lead');
    }

    /**
     * Generates merge form and action.
     *
     * @param $objectId
     *
     * @return array|JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function mergeAction($objectId)
    {
        /** @var \Milex\LeadBundle\Model\LeadModel $model */
        $model    = $this->getModel('lead');
        $mainLead = $model->getEntity($objectId);
        $page     = $this->get('session')->get('milex.lead.page', 1);

        //set the return URL
        $returnUrl = $this->generateUrl('milex_contact_index', ['page' => $page]);

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MilexLeadBundle:Lead:index',
            'passthroughVars' => [
                'activeLink'    => '#milex_contact_index',
                'milexContent' => 'lead',
            ],
        ];

        if (null === $mainLead) {
            return $this->postActionRedirect(
                array_merge(
                    $postActionVars,
                    [
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => 'milex.lead.lead.error.notfound',
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ]
                )
            );
        }

        //do some default filtering
        $session = $this->get('session');
        $search  = $this->request->get('search', $session->get('milex.lead.merge.filter', ''));
        $session->set('milex.lead.merge.filter', $search);
        $leads = [];

        if (!empty($search)) {
            $filter = [
                'string' => $search,
                'force'  => [
                    [
                        'column' => 'l.date_identified',
                        'expr'   => 'isNotNull',
                        'value'  => $mainLead->getId(),
                    ],
                    [
                        'column' => 'l.id',
                        'expr'   => 'neq',
                        'value'  => $mainLead->getId(),
                    ],
                ],
            ];

            $leads = $model->getEntities(
                [
                    'limit'          => 25,
                    'filter'         => $filter,
                    'orderBy'        => 'l.firstname,l.lastname,l.company,l.email',
                    'orderByDir'     => 'ASC',
                    'withTotalCount' => false,
                ]
            );
        }

        $leadChoices = [];
        foreach ($leads as $l) {
            $leadChoices[$l->getPrimaryIdentifier()] = $l->getId();
        }

        $action = $this->generateUrl('milex_contact_action', ['objectAction' => 'merge', 'objectId' => $mainLead->getId()]);

        $form = $this->get('form.factory')->create(
            MergeType::class,
            [],
            [
                'action' => $action,
                'leads'  => $leadChoices,
            ]
        );

        if ('POST' == $this->request->getMethod()) {
            $valid = true;
            if (!$this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $data      = $form->getData();
                    $secLeadId = $data['lead_to_merge'];
                    $secLead   = $model->getEntity($secLeadId);

                    if (null === $secLead) {
                        return $this->postActionRedirect(
                            array_merge(
                                $postActionVars,
                                [
                                    'flashes' => [
                                        [
                                            'type'    => 'error',
                                            'msg'     => 'milex.lead.lead.error.notfound',
                                            'msgVars' => ['%id%' => $secLead->getId()],
                                        ],
                                    ],
                                ]
                            )
                        );
                    } elseif (
                        !$this->get('milex.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $mainLead->getPermissionUser())
                        || !$this->get('milex.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $secLead->getPermissionUser())
                    ) {
                        return $this->accessDenied();
                    } elseif ($model->isLocked($mainLead)) {
                        //deny access if the entity is locked
                        return $this->isLocked($postActionVars, $secLead, 'lead');
                    } elseif ($model->isLocked($secLead)) {
                        //deny access if the entity is locked
                        return $this->isLocked($postActionVars, $secLead, 'lead');
                    }

                    //Both leads are good so now we merge them
                    /** @var ContactMerger $contactMerger */
                    $contactMerger = $this->container->get('milex.lead.merger');
                    try {
                        $mainLead = $contactMerger->merge($mainLead, $secLead);
                    } catch (SameContactException $exception) {
                    }
                }
            }

            if ($valid) {
                $viewParameters = [
                    'objectId'     => $mainLead->getId(),
                    'objectAction' => 'view',
                ];

                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $this->generateUrl('milex_contact_action', $viewParameters),
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => 'MilexLeadBundle:Lead:view',
                        'passthroughVars' => [
                            'closeModal' => 1,
                        ],
                    ]
                );
            }
        }

        $tmpl = $this->request->get('tmpl', 'index');

        return $this->delegateView(
            [
                'viewParameters' => [
                    'tmpl'         => $tmpl,
                    'leads'        => $leads,
                    'searchValue'  => $search,
                    'action'       => $action,
                    'form'         => $form->createView(),
                    'currentRoute' => $this->generateUrl(
                        'milex_contact_action',
                        [
                            'objectAction' => 'merge',
                            'objectId'     => $mainLead->getId(),
                        ]
                    ),
                ],
                'contentTemplate' => 'MilexLeadBundle:Lead:merge.html.php',
                'passthroughVars' => [
                    'route'  => false,
                    'target' => ('update' == $tmpl) ? '.lead-merge-options' : null,
                ],
            ]
        );
    }

    /**
     * Generates contact frequency rules form and action.
     *
     * @param $objectId
     *
     * @return array|JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function contactFrequencyAction($objectId)
    {
        /** @var LeadModel $model */
        $model = $this->getModel('lead');
        $lead  = $model->getEntity($objectId);

        if (null === $lead
            || !$this->get('milex.security')->hasEntityAccess(
                'lead:leads:editown',
                'lead:leads:editother',
                $lead->getPermissionUser()
            )
        ) {
            return $this->accessDenied();
        }

        $viewParameters = [
            'objectId'     => $lead->getId(),
            'objectAction' => 'view',
        ];

        $form = $this->getFrequencyRuleForm(
            $lead,
            $viewParameters,
            $data,
            false,
            $this->generateUrl('milex_contact_action', ['objectAction' => 'contactFrequency', 'objectId' => $lead->getId()])
        );

        if (true === $form) {
            return $this->postActionRedirect(
                [
                    'returnUrl' => $this->generateUrl('milex_contact_action', [
                        'objectId'     => $lead->getId(),
                        'objectAction' => 'view',
                    ]),
                    'viewParameters'  => $viewParameters,
                    'contentTemplate' => 'MilexLeadBundle:Lead:view',
                    'passthroughVars' => [
                        'closeModal' => 1,
                    ],
                ]
            );
        }

        $tmpl = $this->request->get('tmpl', 'index');

        return $this->delegateView(
            [
                'viewParameters' => array_merge(
                    [
                        'tmpl'         => $tmpl,
                        'form'         => $form->createView(),
                        'currentRoute' => $this->generateUrl(
                            'milex_contact_action',
                            [
                                'objectAction' => 'contactFrequency',
                                'objectId'     => $lead->getId(),
                            ]
                        ),
                        'lead' => $lead,
                    ],
                    $viewParameters
                ),
                'contentTemplate' => 'MilexLeadBundle:Lead:frequency.html.php',
                'passthroughVars' => [
                    'route'  => false,
                    'target' => ('update' == $tmpl) ? '.lead-frequency-options' : null,
                ],
            ]
        );
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
        $page      = $this->get('session')->get('milex.lead.page', 1);
        $returnUrl = $this->generateUrl('milex_contact_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MilexLeadBundle:Lead:index',
            'passthroughVars' => [
                'activeLink'    => '#milex_contact_index',
                'milexContent' => 'lead',
            ],
        ];

        if ('POST' == $this->request->getMethod()) {
            $model  = $this->getModel('lead.lead');
            $entity = $model->getEntity($objectId);

            if (null === $entity) {
                $flashes[] = [
                    'type'    => 'error',
                    'msg'     => 'milex.lead.lead.error.notfound',
                    'msgVars' => ['%id%' => $objectId],
                ];
            } elseif (!$this->get('milex.security')->hasEntityAccess(
                'lead:leads:deleteown',
                'lead:leads:deleteother',
                $entity->getPermissionUser()
            )
            ) {
                return $this->accessDenied();
            } elseif ($model->isLocked($entity)) {
                return $this->isLocked($postActionVars, $entity, 'lead.lead');
            } else {
                $model->deleteEntity($entity);

                $identifier = $this->get('translator')->trans($entity->getPrimaryIdentifier());
                $flashes[]  = [
                    'type'    => 'notice',
                    'msg'     => 'milex.core.notice.deleted',
                    'msgVars' => [
                        '%name%' => $identifier,
                        '%id%'   => $objectId,
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
     * Deletes a group of entities.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchDeleteAction()
    {
        $page      = $this->get('session')->get('milex.lead.page', 1);
        $returnUrl = $this->generateUrl('milex_contact_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'MilexLeadBundle:Lead:index',
            'passthroughVars' => [
                'activeLink'    => '#milex_contact_index',
                'milexContent' => 'lead',
            ],
        ];

        if ('POST' == $this->request->getMethod()) {
            $model     = $this->getModel('lead');
            $ids       = json_decode($this->request->query->get('ids', '{}'));
            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if (null === $entity) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => 'milex.lead.lead.error.notfound',
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('milex.security')->hasEntityAccess(
                    'lead:leads:deleteown',
                    'lead:leads:deleteother',
                    $entity->getPermissionUser()
                )
                ) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'lead', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => 'milex.lead.lead.notice.batch_deleted',
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
     * Add/remove lead from a list.
     *
     * @param $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function listAction($objectId)
    {
        /** @var \Milex\LeadBundle\Model\LeadModel $model */
        $model = $this->getModel('lead');
        $lead  = $model->getEntity($objectId);

        if (null != $lead
            && $this->get('milex.security')->hasEntityAccess(
                'lead:leads:editown',
                'lead:leads:editother',
                $lead->getPermissionUser()
            )
        ) {
            /** @var \Milex\LeadBundle\Model\ListModel $listModel */
            $listModel = $this->getModel('lead.list');
            $lists     = $listModel->getUserLists();

            // Get a list of lists for the lead
            $leadsLists = $model->getLists($lead, true, true);
        } else {
            $lists = $leadsLists = [];
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'lists'      => $lists,
                    'leadsLists' => $leadsLists,
                    'lead'       => $lead,
                ],
                'contentTemplate' => 'MilexLeadBundle:LeadLists:index.html.php',
            ]
        );
    }

    /**
     * Add/remove lead from a company.
     *
     * @param $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function companyAction($objectId)
    {
        /** @var \Milex\LeadBundle\Model\LeadModel $model */
        $model = $this->getModel('lead');
        $lead  = $model->getEntity($objectId);

        if (null != $lead
            && $this->get('milex.security')->hasEntityAccess(
                'lead:leads:editown',
                'lead:leads:editother',
                $lead->getOwner()
            )
        ) {
            /** @var \Milex\LeadBundle\Model\CompanyModel $companyModel */
            $companyModel = $this->getModel('lead.company');
            $companies    = $companyModel->getUserCompanies();

            // Get a list of lists for the lead
            $companyLeads = $lead->getCompanies();
            foreach ($companyLeads as $cl) {
                $companyLead[$cl->getId()] = $cl->getId();
            }
        } else {
            $companies = $companyLead = [];
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'companies'   => $companies,
                    'companyLead' => $companyLead,
                    'lead'        => $lead,
                ],
                'contentTemplate' => 'MilexLeadBundle:Lead:company.html.php',
            ]
        );
    }

    /**
     * Add/remove lead from a campaign.
     *
     * @param $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function campaignAction($objectId)
    {
        $model = $this->getModel('lead');
        $lead  = $model->getEntity($objectId);

        if (null != $lead
            && $this->get('milex.security')->hasEntityAccess(
                'lead:leads:editown',
                'lead:leads:editother',
                $lead->getPermissionUser()
            )
        ) {
            /** @var \Milex\CampaignBundle\Model\CampaignModel $campaignModel */
            $campaignModel  = $this->getModel('campaign');
            $campaigns      = $campaignModel->getPublishedCampaigns(true);
            $leadsCampaigns = $campaignModel->getLeadCampaigns($lead, true);

            foreach ($campaigns as $c) {
                $campaigns[$c['id']]['inCampaign'] = (isset($leadsCampaigns[$c['id']])) ? true : false;
            }
        } else {
            $campaigns = [];
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'campaigns' => $campaigns,
                    'lead'      => $lead,
                ],
                'contentTemplate' => 'MilexLeadBundle:LeadCampaigns:index.html.php',
            ]
        );
    }

    /**
     * @param int $objectId
     *
     * @return JsonResponse
     */
    public function emailAction($objectId = 0)
    {
        $valid = $cancelled = false;

        /** @var \Milex\LeadBundle\Model\LeadModel $model */
        $model = $this->getModel('lead');

        /** @var \Milex\LeadBundle\Entity\Lead $lead */
        $lead = $model->getEntity($objectId);

        if (null === $lead
            || !$this->get('milex.security')->hasEntityAccess(
                'lead:leads:viewown',
                'lead:leads:viewother',
                $lead->getPermissionUser()
            )
        ) {
            return $this->modalAccessDenied();
        }

        $leadFields       = $lead->getProfileFields();
        $leadFields['id'] = $lead->getId();
        $leadEmail        = $leadFields['email'];
        $leadName         = $leadFields['firstname'].' '.$leadFields['lastname'];
        $mailerIsOwner    = $this->get('milex.helper.core_parameters')->getParameter('mailer_is_owner');

        // Set onwer ID to be the current user ID so it will use his signature
        $leadFields['owner_id'] = $this->get('milex.helper.user')->getUser()->getId();

        $inList = ('GET' == $this->request->getMethod())
            ? $this->request->get('list', 0)
            : $this->request->request->get(
                'lead_quickemail[list]',
                0,
                true
            );
        $email = ['list' => $inList];

        // Try set owner If should be mailer
        if ($lead->getOwner()) {
            $leadFields['owner_id'] = $lead->getOwner()->getId();
            if ($mailerIsOwner) {
                $email['fromname'] = sprintf(
                    '%s %s',
                    $lead->getOwner()->getFirstName(),
                    $lead->getOwner()->getLastName()
                );
                $email['from'] = $lead->getOwner()->getEmail();
            }
        }

        // Check if lead has a bounce status
        $dnc    = $this->getDoctrine()->getManager()->getRepository('MilexLeadBundle:DoNotContact')->getEntriesByLeadAndChannel($lead, 'email');
        $action = $this->generateUrl('milex_contact_action', ['objectAction' => 'email', 'objectId' => $objectId]);
        $form   = $this->get('form.factory')->create(EmailType::class, $email, ['action' => $action]);

        if ('POST' == $this->request->getMethod()) {
            $valid = false;
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $email = $form->getData();

                    $bodyCheck = trim(strip_tags($email['body']));
                    if (!empty($bodyCheck)) {
                        $mailer = $this->get('milex.helper.mailer')->getMailer();

                        // To lead
                        $mailer->addTo($leadEmail, $leadName);

                        // From user
                        $user = $this->get('milex.helper.user')->getUser();

                        $mailer->setFrom(
                            $email['from'],
                            empty($email['fromname']) ? null : $email['fromname']
                        );

                        // Set Content
                        $mailer->setBody($email['body']);
                        $mailer->parsePlainText($email['body']);

                        // Set lead
                        $mailer->setLead($leadFields);
                        $mailer->setIdHash();

                        $mailer->setSubject($email['subject']);

                        // Ensure safe emoji for notification
                        $subject = EmojiHelper::toHtml($email['subject']);
                        if ($mailer->send(true, false, false)) {
                            $mailer->createEmailStat();
                            $this->addFlash(
                                'milex.lead.email.notice.sent',
                                [
                                    '%subject%' => $subject,
                                    '%email%'   => $leadEmail,
                                ]
                            );
                        } else {
                            $errors = $mailer->getErrors();

                            // Unset the array of failed email addresses
                            if (isset($errors['failures'])) {
                                unset($errors['failures']);
                            }

                            $form->addError(
                                new FormError(
                                    $this->get('translator')->trans(
                                        'milex.lead.email.error.failed',
                                        [
                                            '%subject%' => $subject,
                                            '%email%'   => $leadEmail,
                                            '%error%'   => (is_array($errors)) ? implode('<br />', $errors) : $errors,
                                        ],
                                        'flashes'
                                    )
                                )
                            );
                            $valid = false;
                        }
                    } else {
                        $form['body']->addError(
                            new FormError(
                                $this->get('translator')->trans('milex.lead.email.body.required', [], 'validators')
                            )
                        );
                        $valid = false;
                    }
                }
            }
        }

        if (empty($leadEmail) || $valid || $cancelled) {
            if ($inList) {
                $route          = 'milex_contact_index';
                $viewParameters = [
                    'page' => $this->get('session')->get('milex.lead.page', 1),
                ];
                $func = 'index';
            } else {
                $route          = 'milex_contact_action';
                $viewParameters = [
                    'objectAction' => 'view',
                    'objectId'     => $objectId,
                ];
                $func = 'view';
            }

            return $this->postActionRedirect(
                [
                    'returnUrl'       => $this->generateUrl($route, $viewParameters),
                    'viewParameters'  => $viewParameters,
                    'contentTemplate' => 'MilexLeadBundle:Lead:'.$func,
                    'passthroughVars' => [
                        'milexContent' => 'lead',
                        'closeModal'    => 1,
                    ],
                ]
            );
        }

        return $this->ajaxAction(
            [
                'contentTemplate' => 'MilexLeadBundle:Lead:email.html.php',
                'viewParameters'  => [
                    'form' => $form->createView(),
                    'dnc'  => end($dnc),
                ],
                'passthroughVars' => [
                    'milexContent' => 'leadEmail',
                    'route'         => false,
                ],
            ]
        );
    }

    /**
     * Bulk edit lead campaigns.
     *
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function batchCampaignsAction($objectId = 0)
    {
        /** @var \Milex\CampaignBundle\Model\CampaignModel $campaignModel */
        $campaignModel = $this->getModel('campaign');

        if ('POST' == $this->request->getMethod()) {
            /** @var \Milex\LeadBundle\Model\LeadModel $model */
            $model = $this->getModel('lead');
            $data  = $this->request->request->get('lead_batch', [], true);
            $ids   = json_decode($data['ids'], true);

            $entities = [];
            if (is_array($ids)) {
                $entities = $model->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'l.id',
                                    'expr'   => 'in',
                                    'value'  => $ids,
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );
            }

            foreach ($entities as $key => $lead) {
                if (!$this->get('milex.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $lead->getPermissionUser())) {
                    unset($entities[$key]);
                }
            }

            $add    = (!empty($data['add'])) ? $data['add'] : [];
            $remove = (!empty($data['remove'])) ? $data['remove'] : [];

            if ($count = count($entities)) {
                $campaigns = $campaignModel->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'c.id',
                                    'expr'   => 'in',
                                    'value'  => array_merge($add, $remove),
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );

                /** @var \Milex\CampaignBundle\Membership\MembershipManager $membershipManager */
                $membershipManager = $this->get('milex.campaign.membership.manager');

                if (!empty($add)) {
                    foreach ($add as $cid) {
                        $membershipManager->addContacts(new ArrayCollection($entities), $campaigns[$cid]);
                    }
                }

                if (!empty($remove)) {
                    foreach ($remove as $cid) {
                        $membershipManager->removeContacts(new ArrayCollection($entities), $campaigns[$cid]);
                    }
                }
            }

            $this->addFlash(
                'milex.lead.batch_leads_affected',
                [
                    '%count%'     => $count,
                ]
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        } else {
            // Get a list of campaigns
            $campaigns = $campaignModel->getPublishedCampaigns(true);
            $items     = [];
            foreach ($campaigns as $campaign) {
                $items[$campaign['name']] = $campaign['id'];
            }

            $route = $this->generateUrl(
                'milex_contact_action',
                [
                    'objectAction' => 'batchCampaigns',
                ]
            );

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            BatchType::class,
                            [],
                            [
                                'items'  => $items,
                                'action' => $route,
                            ]
                        )->createView(),
                    ],
                    'contentTemplate' => 'MilexLeadBundle:Batch:form.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#milex_contact_index',
                        'milexContent' => 'leadBatch',
                        'route'         => $route,
                    ],
                ]
            );
        }
    }

    /**
     * Bulk add leads to the DNC list.
     *
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function batchDncAction($objectId = 0)
    {
        if ('POST' == $this->request->getMethod()) {
            /** @var \Milex\LeadBundle\Model\LeadModel $model */
            $model = $this->getModel('lead');

            /** @var \Milex\LeadBundle\Model\DoNotContact $doNotContact */
            $doNotContact = $this->get('milex.lead.model.dnc');

            $data = $this->request->request->get('lead_batch_dnc', [], true);
            $ids  = json_decode($data['ids'], true);

            $entities = [];
            if (is_array($ids)) {
                $entities = $model->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'l.id',
                                    'expr'   => 'in',
                                    'value'  => $ids,
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );
            }

            if ($count = count($entities)) {
                $persistEntities = [];
                foreach ($entities as $lead) {
                    if ($this->get('milex.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $lead->getPermissionUser())) {
                        if ($doNotContact->addDncForContact($lead->getId(), 'email', DoNotContact::MANUAL, $data['reason'])) {
                            $persistEntities[] = $lead;
                        }
                    }
                }

                // Save entities
                $model->saveEntities($persistEntities);
            }

            $this->addFlash(
                'milex.lead.batch_leads_affected',
                [
                    '%count%'     => $count,
                ]
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        } else {
            $route = $this->generateUrl(
                'milex_contact_action',
                [
                    'objectAction' => 'batchDnc',
                ]
            );

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            DncType::class,
                            [],
                            [
                                'action' => $route,
                            ]
                        )->createView(),
                    ],
                    'contentTemplate' => 'MilexLeadBundle:Batch:form.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#milex_contact_index',
                        'milexContent' => 'leadBatch',
                        'route'         => $route,
                    ],
                ]
            );
        }
    }

    /**
     * Bulk edit lead stages.
     *
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function batchStagesAction($objectId = 0)
    {
        if ('POST' == $this->request->getMethod()) {
            /** @var \Milex\LeadBundle\Model\LeadModel $model */
            $model = $this->getModel('lead');
            $data  = $this->request->request->get('lead_batch_stage', [], true);
            $ids   = json_decode($data['ids'], true);

            $entities = [];
            if (is_array($ids)) {
                $entities = $model->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'l.id',
                                    'expr'   => 'in',
                                    'value'  => $ids,
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );
            }

            $count = 0;
            foreach ($entities as $lead) {
                if ($this->get('milex.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $lead->getPermissionUser())) {
                    ++$count;

                    if (!empty($data['addstage'])) {
                        $stageModel = $this->getModel('stage');

                        $stage = $stageModel->getEntity((int) $data['addstage']);
                        $model->addToStages($lead, $stage);
                    }

                    if (!empty($data['removestage'])) {
                        $stage = $stageModel->getEntity($data['removestage']);
                        $model->removeFromStages($lead, $stage);
                    }
                }
            }
            // Save entities
            $model->saveEntities($entities);
            $this->addFlash(
                'milex.lead.batch_leads_affected',
                [
                    '%count%'     => $count,
                ]
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        } else {
            // Get a list of lists
            /** @var \Milex\StageBundle\Model\StageModel $model */
            $model  = $this->getModel('stage');
            $stages = $model->getUserStages();
            $items  = [];
            foreach ($stages as $stage) {
                $items[$stage['name']] = $stage['id'];
            }

            $route = $this->generateUrl(
                'milex_contact_action',
                [
                    'objectAction' => 'batchStages',
                ]
            );

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            StageType::class,
                            [],
                            [
                                'items'  => $items,
                                'action' => $route,
                            ]
                        )->createView(),
                    ],
                    'contentTemplate' => 'MilexLeadBundle:Batch:form.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#milex_contact_index',
                        'milexContent' => 'leadBatch',
                        'route'         => $route,
                    ],
                ]
            );
        }
    }

    /**
     * Bulk edit lead owner.
     *
     * @param int $objectId
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function batchOwnersAction($objectId = 0)
    {
        if ('POST' == $this->request->getMethod()) {
            /** @var \Milex\LeadBundle\Model\LeadModel $model */
            $model = $this->getModel('lead');
            $data  = $this->request->request->get('lead_batch_owner', [], true);
            $ids   = json_decode($data['ids'], true);

            $entities = [];
            if (is_array($ids)) {
                $entities = $model->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'l.id',
                                    'expr'   => 'in',
                                    'value'  => $ids,
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );
            }
            $count = 0;
            foreach ($entities as $lead) {
                if ($this->get('milex.security')->hasEntityAccess('lead:leads:editown', 'lead:leads:editother', $lead->getPermissionUser())) {
                    ++$count;

                    if (!empty($data['addowner'])) {
                        $userModel = $this->getModel('user');
                        $user      = $userModel->getEntity((int) $data['addowner']);
                        $lead->setOwner($user);
                    }
                }
            }
            // Save entities
            $model->saveEntities($entities);
            $this->addFlash(
                'milex.lead.batch_leads_affected',
                [
                    '%count%'     => $count,
                ]
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        } else {
            $users = $this->getModel('user.user')->getRepository()->getUserList('', 0);
            $items = [];
            foreach ($users as $user) {
                $items[$user['firstName'].' '.$user['lastName']] = $user['id'];
            }

            $route = $this->generateUrl(
                'milex_contact_action',
                [
                    'objectAction' => 'batchOwners',
                ]
            );

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            OwnerType::class,
                            [],
                            [
                                'items'  => $items,
                                'action' => $route,
                            ]
                        )->createView(),
                    ],
                    'contentTemplate' => 'MilexLeadBundle:Batch:form.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#milex_contact_index',
                        'milexContent' => 'leadBatch',
                        'route'         => $route,
                    ],
                ]
            );
        }
    }

    /**
     * Bulk export contacts.
     *
     * @return array|JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function batchExportAction()
    {
        //set some permissions
        $permissions = $this->get('milex.security')->isGranted(
            [
                'lead:leads:viewown',
                'lead:leads:viewother',
                'lead:leads:create',
                'lead:leads:editown',
                'lead:leads:editother',
                'lead:leads:deleteown',
                'lead:leads:deleteother',
            ],
            'RETURN_ARRAY'
        );

        if (!$permissions['lead:leads:viewown'] && !$permissions['lead:leads:viewother']) {
            return $this->accessDenied();
        }

        /** @var \Milex\LeadBundle\Model\LeadModel $model */
        $model      = $this->getModel('lead');
        $session    = $this->get('session');
        $search     = $session->get('milex.lead.filter', '');
        $orderBy    = $session->get('milex.lead.orderby', 'l.last_active');
        // Add an id field to orderBy. Prevent Null-value ordering
        $orderById  = 'l.id' !== $orderBy ? ', l.id' : '';
        $orderBy    = $orderBy.$orderById;
        $orderByDir = $session->get('milex.lead.orderbydir', 'DESC');
        $ids        = $this->request->get('ids');

        $filter     = ['string' => $search, 'force' => ''];
        $translator = $this->get('translator');
        $anonymous  = $translator->trans('milex.lead.lead.searchcommand.isanonymous');
        $mine       = $translator->trans('milex.core.searchcommand.ismine');
        $indexMode  = $session->get('milex.lead.indexmode', 'list');
        $dataType   = $this->request->get('filetype');

        if (!empty($ids)) {
            $filter['force'] = [
                [
                    'column' => 'l.id',
                    'expr'   => 'in',
                    'value'  => json_decode($ids, true),
                ],
            ];
        } else {
            if ('list' != $indexMode || ('list' == $indexMode && false === strpos($search, $anonymous))) {
                //remove anonymous leads unless requested to prevent clutter
                $filter['force'] .= " !$anonymous";
            }

            if (!$permissions['lead:leads:viewother']) {
                $filter['force'] .= " $mine";
            }
        }

        $args = [
            'start'          => 0,
            'limit'          => 200,
            'filter'         => $filter,
            'orderBy'        => $orderBy,
            'orderByDir'     => $orderByDir,
            'withTotalCount' => true,
        ];

        $resultsCallback = function ($contact) {
            return $contact->getProfileFields();
        };

        $iterator = new IteratorExportDataModel($model, $args, $resultsCallback);

        return $this->exportResultsAs($iterator, $dataType, 'contacts');
    }

    /**
     * @param $contactId
     *
     * @return array|JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function contactExportAction($contactId)
    {
        //set some permissions
        $permissions = $this->get('milex.security')->isGranted(
            [
                'lead:leads:viewown',
                'lead:leads:viewother',
            ],
            'RETURN_ARRAY'
        );

        if (!$permissions['lead:leads:viewown'] && !$permissions['lead:leads:viewother']) {
            return $this->accessDenied();
        }

        /** @var LeadModel $leadModel */
        $leadModel = $this->getModel('lead.lead');
        $lead      = $leadModel->getEntity($contactId);
        $dataType  = $this->request->get('filetype', 'csv');

        if (empty($lead)) {
            return $this->notFound();
        }

        $contactFields = $lead->getProfileFields();
        $export        = [];
        foreach ($contactFields as $alias => $contactField) {
            $export[] = [
                'alias' => $alias,
                'value' => $contactField,
            ];
        }

        return $this->exportResultsAs($export, $dataType, 'contact_data_'.($contactFields['email'] ?: $contactFields['id']));
    }
}
