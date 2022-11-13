<?php

namespace Milex\PointBundle\Controller;

use Milex\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Milex\CoreBundle\Helper\InputHelper;
use Milex\PointBundle\Form\Type\GenericPointSettingsType;
use Milex\PointBundle\Form\Type\PointActionType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AjaxController.
 */
class AjaxController extends CommonAjaxController
{
    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function reorderTriggerEventsAction(Request $request)
    {
        $dataArray   = ['success' => 0];
        $session     = $this->get('session');
        $triggerId   = InputHelper::clean($request->request->get('triggerId'));
        $sessionName = 'milex.point.'.$triggerId.'.triggerevents.modified';
        $order       = InputHelper::clean($request->request->get('triggerEvent'));
        $components  = $session->get($sessionName);
        if (!empty($order) && !empty($components)) {
            $components = array_replace(array_flip($order), $components);
            $session->set($sessionName, $components);
            $dataArray['success'] = 1;
        }

        return $this->sendJsonResponse($dataArray);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function getActionFormAction(Request $request)
    {
        $dataArray = [
            'success' => 0,
            'html'    => '',
        ];
        $type = InputHelper::clean($request->request->get('actionType'));

        if (!empty($type)) {
            //get the HTML for the form
            /** @var \Milex\PointBundle\Model\PointModel $model */
            $model   = $this->getModel('point');
            $actions = $model->getPointActions();

            if (isset($actions['actions'][$type])) {
                $themes = ['MilexPointBundle:FormTheme\Action'];
                if (!empty($actions['actions'][$type]['formTheme'])) {
                    $themes[] = $actions['actions'][$type]['formTheme'];
                }

                $formType        = (!empty($actions['actions'][$type]['formType'])) ? $actions['actions'][$type]['formType'] : GenericPointSettingsType::class;
                $formTypeOptions = (!empty($actions['actions'][$type]['formTypeOptions'])) ? $actions['actions'][$type]['formTypeOptions'] : [];
                $form            = $this->get('form.factory')->create(PointActionType::class, [], ['formType' => $formType, 'formTypeOptions' => $formTypeOptions]);
                $html            = $this->renderView('MilexPointBundle:Point:actionform.html.php', [
                    'form' => $this->setFormTheme($form, 'MilexPointBundle:Point:actionform.html.php', $themes),
                ]);

                //replace pointaction with point
                $html                 = str_replace('pointaction', 'point', $html);
                $dataArray['html']    = $html;
                $dataArray['success'] = 1;
            }
        }

        return $this->sendJsonResponse($dataArray);
    }
}
