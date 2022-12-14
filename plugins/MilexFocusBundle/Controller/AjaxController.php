<?php

namespace MilexPlugin\MilexFocusBundle\Controller;

use Milex\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Milex\CoreBundle\Helper\InputHelper;
use MilexPlugin\MilexFocusBundle\Helper\IframeAvailabilityChecker;
use MilexPlugin\MilexFocusBundle\Model\FocusModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends CommonAjaxController
{
    /**
     * This method produces HTTP request checking headers which are blocking availability for iframe inheritance for other pages.
     */
    protected function checkIframeAvailabilityAction(Request $request): JsonResponse
    {
        $url = $request->request->get('website');

        /** @var IframeAvailabilityChecker $availabilityChecker */
        $availabilityChecker = $this->get('milex.focus.helper.iframe_availability_checker');

        return $availabilityChecker->check($url, $request->getScheme());
    }

    protected function generatePreviewAction(Request $request): JsonResponse
    {
        $responseContent  = ['html' => '', 'style' => ''];
        $focus            = $request->request->all();

        if (isset($focus['focus'])) {
            $focusArray = InputHelper::_($focus['focus']);

            if (!empty($focusArray['style']) && !empty($focusArray['type'])) {
                /** @var FocusModel $model */
                $model                    = $this->getModel('focus');
                $focusArray['id']         = 'preview';
                $responseContent['html']  = $model->getContent($focusArray, true);
                $responseContent['style'] = $focusArray['style']; // Required by JS in response
            }
        }

        return $this->sendJsonResponse($responseContent);
    }
}
