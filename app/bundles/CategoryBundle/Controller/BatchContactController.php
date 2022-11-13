<?php

namespace Milex\CategoryBundle\Controller;

use Milex\CategoryBundle\Model\CategoryModel;
use Milex\CategoryBundle\Model\ContactActionModel;
use Milex\CoreBundle\Controller\AbstractFormController;
use Milex\LeadBundle\Form\Type\BatchType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

class BatchContactController extends AbstractFormController
{
    /**
     * @var ContactActionModel
     */
    private $actionModel;

    /**
     * @var CategoryModel
     */
    private $categoryModel;

    /**
     * Initialize object props here to simulate constructor
     * and make the future controller refactoring easier.
     */
    public function initialize(FilterControllerEvent $event)
    {
        $this->actionModel   = $this->container->get('milex.category.model.contact.action');
        $this->categoryModel = $this->container->get('milex.category.model.category');
    }

    /**
     * Adds or removes categories to multiple contacts defined by contact ID.
     *
     * @return JsonResponse
     */
    public function execAction()
    {
        $params = $this->request->get('lead_batch');
        $ids    = empty($params['ids']) ? [] : json_decode($params['ids']);

        if ($ids && is_array($ids)) {
            $categoriesToAdd    = isset($params['add']) ? $params['add'] : [];
            $categoriesToRemove = isset($params['remove']) ? $params['remove'] : [];
            $contactIds         = json_decode($params['ids']);

            $this->actionModel->addContactsToCategories($contactIds, $categoriesToAdd);
            $this->actionModel->removeContactsFromCategories($contactIds, $categoriesToRemove);

            $this->addFlash('milex.lead.batch_leads_affected', [
                '%count%'     => count($ids),
            ]);
        } else {
            $this->addFlash('milex.core.error.ids.missing');
        }

        return new JsonResponse([
            'closeModal' => true,
            'flashes'    => $this->getFlashContent(),
        ]);
    }

    /**
     * View the modal form for adding contacts into categories in batches.
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $route = $this->generateUrl('milex_category_batch_contact_set');
        $rows  = $this->categoryModel->getLookupResults('global', '', 300);
        $items = [];

        foreach ($rows as $category) {
            $items[$category['title']] = $category['id'];
        }

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
