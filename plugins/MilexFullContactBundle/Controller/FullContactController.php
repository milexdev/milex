<?php

namespace MilexPlugin\MilexFullContactBundle\Controller;

use Milex\FormBundle\Controller\FormController;
use Milex\LeadBundle\Entity\Company;
use Milex\LeadBundle\Entity\Lead;
use MilexPlugin\MilexFullContactBundle\Form\Type\BatchLookupType;
use MilexPlugin\MilexFullContactBundle\Form\Type\LookupType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FullContactController extends FormController
{
    /**
     * @param string $objectId
     *
     * @return JsonResponse
     *
     * @throws \InvalidArgumentException
     */
    public function lookupPersonAction($objectId = '')
    {
        if ('POST' === $this->request->getMethod()) {
            $data     = $this->request->request->get('fullcontact_lookup', [], true);
            $objectId = $data['objectId'];
        }
        /** @var \Milex\LeadBundle\Model\LeadModel $model */
        $model = $this->getModel('lead');
        $lead  = $model->getEntity($objectId);

        if (!$this->get('milex.security')->hasEntityAccess(
            'lead:leads:editown',
            'lead:leads:editother',
            $lead->getPermissionUser()
        )
        ) {
            $this->addFlash(
                $this->translator->trans('milex.plugin.fullcontact.forbidden'),
                [],
                'error'
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        }

        if ('GET' === $this->request->getMethod()) {
            $route = $this->generateUrl(
                'milex_plugin_fullcontact_action',
                [
                    'objectAction' => 'lookupPerson',
                ]
            );

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            LookupType::class,
                            [
                                'objectId' => $objectId,
                            ],
                            [
                                'action' => $route,
                            ]
                        )->createView(),
                        'lookupItem' => $lead->getEmail(),
                    ],
                    'contentTemplate' => 'MilexFullContactBundle:FullContact:lookup.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#milex_contact_index',
                        'milexContent' => 'lead',
                        'route'         => $route,
                    ],
                ]
            );
        } else {
            if ('POST' === $this->request->getMethod()) {
                try {
                    $this->get('milex.plugin.fullcontact.lookup_helper')->lookupContact($lead, array_key_exists('notify', $data));
                    $this->addFlash(
                        'milex.lead.batch_leads_affected',
                        [
                            '%count%'     => 1,
                        ]
                    );
                } catch (\Exception $ex) {
                    $this->addFlash(
                        $ex->getMessage(),
                        [],
                        'error'
                    );
                }

                return new JsonResponse(
                    [
                        'closeModal' => true,
                        'flashes'    => $this->getFlashContent(),
                    ]
                );
            }
        }

        return new Response('Bad Request', 400);
    }

    /**
     * @return JsonResponse
     *
     * @throws \InvalidArgumentException
     */
    public function batchLookupPersonAction()
    {
        /** @var \Milex\LeadBundle\Model\LeadModel $model */
        $model = $this->getModel('lead');
        if ('GET' === $this->request->getMethod()) {
            $data = $this->request->query->get('fullcontact_batch_lookup', [], true);
        } else {
            $data = $this->request->request->get('fullcontact_batch_lookup', [], true);
        }

        $entities = [];
        if (array_key_exists('ids', $data)) {
            $ids = $data['ids'];

            if (!is_array($ids)) {
                $ids = json_decode($ids, true);
            }

            if (is_array($ids) && count($ids)) {
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
        }

        $lookupEmails = [];
        if ($count = count($entities)) {
            /** @var Lead $lead */
            foreach ($entities as $lead) {
                if ($this->get('milex.security')->hasEntityAccess(
                        'lead:leads:editown',
                        'lead:leads:editother',
                        $lead->getPermissionUser()
                    )
                    && $lead->getEmail()
                ) {
                    $lookupEmails[$lead->getId()] = $lead->getEmail();
                }
            }

            $count = count($lookupEmails);
        }

        if (0 === $count) {
            $this->addFlash(
                $this->translator->trans('milex.plugin.fullcontact.empty'),
                [],
                'error'
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        } else {
            if ($count > 20) {
                $this->addFlash(
                    $this->translator->trans('milex.plugin.fullcontact.toomany'),
                    [],
                    'error'
                );

                return new JsonResponse(
                    [
                        'closeModal' => true,
                        'flashes'    => $this->getFlashContent(),
                    ]
                );
            }
        }
        if ('GET' === $this->request->getMethod()) {
            $route = $this->generateUrl(
                'milex_plugin_fullcontact_action',
                [
                    'objectAction' => 'batchLookupPerson',
                ]
            );

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            BatchLookupType::class,
                            [],
                            [
                                'action' => $route,
                            ]
                        )->createView(),
                        'lookupItems' => array_values($lookupEmails),
                    ],
                    'contentTemplate' => 'MilexFullContactBundle:FullContact:batchLookup.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#milex_contact_index',
                        'milexContent' => 'leadBatch',
                        'route'         => $route,
                    ],
                ]
            );
        } else {
            if ('POST' === $this->request->getMethod()) {
                $notify = array_key_exists('notify', $data);
                foreach ($lookupEmails as $id => $lookupEmail) {
                    if ($lead = $model->getEntity($id)) {
                        try {
                            $this->get('milex.plugin.fullcontact.lookup_helper')->lookupContact($lead, $notify);
                        } catch (\Exception $ex) {
                            $this->addFlash(
                                $ex->getMessage(),
                                [],
                                'error'
                            );
                            --$count;
                        }
                    }
                }

                if ($count) {
                    $this->addFlash(
                        'milex.lead.batch_leads_affected',
                        [
                            '%count%'     => $count,
                        ]
                    );
                }

                return new JsonResponse(
                    [
                        'closeModal' => true,
                        'flashes'    => $this->getFlashContent(),
                    ]
                );
            }
        }

        return new Response('Bad Request', 400);
    }

    /***************** COMPANY ***********************/

    /**
     * @param string $objectId
     *
     * @return JsonResponse
     *
     * @throws \InvalidArgumentException
     */
    public function lookupCompanyAction($objectId = '')
    {
        if ('POST' === $this->request->getMethod()) {
            $data     = $this->request->request->get('fullcontact_lookup', [], true);
            $objectId = $data['objectId'];
        }
        /** @var \Milex\LeadBundle\Model\CompanyModel $model */
        $model = $this->getModel('lead.company');
        /** @var Company $company */
        $company = $model->getEntity($objectId);

        if ('GET' === $this->request->getMethod()) {
            $route = $this->generateUrl(
                'milex_plugin_fullcontact_action',
                [
                    'objectAction' => 'lookupCompany',
                ]
            );

            $website = $company->getFieldValue('companywebsite');

            if (!$website) {
                $this->addFlash(
                    $this->translator->trans('milex.plugin.fullcontact.compempty'),
                    [],
                    'error'
                );

                return new JsonResponse(
                    [
                        'closeModal' => true,
                        'flashes'    => $this->getFlashContent(),
                    ]
                );
            }
            $parse = parse_url($website);

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            LookupType::class,
                            [
                                'objectId' => $objectId,
                            ],
                            [
                                'action' => $route,
                            ]
                        )->createView(),
                        'lookupItem' => $parse['host'],
                    ],
                    'contentTemplate' => 'MilexFullContactBundle:FullContact:lookup.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#milex_company_index',
                        'milexContent' => 'company',
                        'route'         => $route,
                    ],
                ]
            );
        } else {
            if ('POST' === $this->request->getMethod()) {
                try {
                    $this->get('milex.plugin.fullcontact.lookup_helper')->lookupCompany($company, array_key_exists('notify', $data));
                    $this->addFlash(
                        'milex.company.batch_companies_affected',
                        [
                            '%count%'     => 1,
                        ]
                    );
                } catch (\Exception $ex) {
                    $this->addFlash(
                        $ex->getMessage(),
                        [],
                        'error'
                    );
                }

                return new JsonResponse(
                    [
                        'closeModal' => true,
                        'flashes'    => $this->getFlashContent(),
                    ]
                );
            }
        }

        return new Response('Bad Request', 400);
    }

    /**
     * @return JsonResponse
     *
     * @throws \InvalidArgumentException
     */
    public function batchLookupCompanyAction()
    {
        /** @var \Milex\LeadBundle\Model\CompanyModel $model */
        $model = $this->getModel('lead.company');
        if ('GET' === $this->request->getMethod()) {
            $data = $this->request->query->get('fullcontact_batch_lookup', [], true);
        } else {
            $data = $this->request->request->get('fullcontact_batch_lookup', [], true);
        }

        $entities = [];
        if (array_key_exists('ids', $data)) {
            $ids = $data['ids'];

            if (!is_array($ids)) {
                $ids = json_decode($ids, true);
            }

            if (is_array($ids) && count($ids)) {
                $entities = $model->getEntities(
                    [
                        'filter' => [
                            'force' => [
                                [
                                    'column' => 'comp.id',
                                    'expr'   => 'in',
                                    'value'  => $ids,
                                ],
                            ],
                        ],
                        'ignore_paginator' => true,
                    ]
                );
            }
        }

        $lookupWebsites = [];
        if ($count = count($entities)) {
            /** @var Company $company */
            foreach ($entities as $company) {
                if ($company->getFieldValue('companywebsite')) {
                    $website = $company->getFieldValue('companywebsite');
                    $parse   = parse_url($website);
                    if (!isset($parse['host'])) {
                        continue;
                    }
                    $lookupWebsites[$company->getId()] = $parse['host'];
                }
            }

            $count = count($lookupWebsites);
        }

        if (0 === $count) {
            $this->addFlash(
                $this->translator->trans('milex.plugin.fullcontact.compempty'),
                [],
                'error'
            );

            return new JsonResponse(
                [
                    'closeModal' => true,
                    'flashes'    => $this->getFlashContent(),
                ]
            );
        } else {
            if ($count > 20) {
                $this->addFlash(
                    $this->translator->trans('milex.plugin.fullcontact.comptoomany'),
                    [],
                    'error'
                );

                return new JsonResponse(
                    [
                        'closeModal' => true,
                        'flashes'    => $this->getFlashContent(),
                    ]
                );
            }
        }
        if ('GET' === $this->request->getMethod()) {
            $route = $this->generateUrl(
                'milex_plugin_fullcontact_action',
                [
                    'objectAction' => 'batchLookupCompany',
                ]
            );

            return $this->delegateView(
                [
                    'viewParameters' => [
                        'form' => $this->createForm(
                            BatchLookupType::class,
                            [],
                            [
                                'action' => $route,
                            ]
                        )->createView(),
                        'lookupItems' => array_values($lookupWebsites),
                    ],
                    'contentTemplate' => 'MilexFullContactBundle:FullContact:batchLookup.html.php',
                    'passthroughVars' => [
                        'activeLink'    => '#milex_company_index',
                        'milexContent' => 'companyBatch',
                        'route'         => $route,
                    ],
                ]
            );
        } else {
            if ('POST' === $this->request->getMethod()) {
                $notify = array_key_exists('notify', $data);
                foreach ($lookupWebsites as $id => $lookupWebsite) {
                    if ($company = $model->getEntity($id)) {
                        try {
                            $this->get('milex.plugin.fullcontact.lookup_helper')->lookupCompany($company, $notify);
                        } catch (\Exception $ex) {
                            $this->addFlash(
                                $ex->getMessage(),
                                [],
                                'error'
                            );
                            --$count;
                        }
                    }
                }

                if ($count) {
                    $this->addFlash(
                        'milex.company.batch_companies_affected',
                        [
                            '%count%'     => $count,
                        ]
                    );
                }

                return new JsonResponse(
                    [
                        'closeModal' => true,
                        'flashes'    => $this->getFlashContent(),
                    ]
                );
            }
        }

        return new Response('Bad Request', 400);
    }
}
