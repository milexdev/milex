<?php

namespace Milex\ConfigBundle\Controller;

use Milex\CoreBundle\Controller\FormController;
use Symfony\Component\HttpFoundation\JsonResponse;

class SysinfoController extends FormController
{
    /**
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        if (!$this->user->isAdmin() || $this->coreParametersHelper->get('sysinfo_disabled')) {
            return $this->accessDenied();
        }

        /** @var \Milex\ConfigBundle\Model\SysinfoModel $model */
        $model = $this->getModel('config.sysinfo');

        return $this->delegateView([
            'viewParameters' => [
                'phpInfo'         => $model->getPhpInfo(),
                'requirements'    => $model->getRequirements(),
                'recommendations' => $model->getRecommendations(),
                'folders'         => $model->getFolders(),
                'log'             => $model->getLogTail(200),
                'dbInfo'          => $model->getDbInfo(),
            ],
            'contentTemplate' => 'MilexConfigBundle:Sysinfo:index.html.php',
            'passthroughVars' => [
                'activeLink'    => '#milex_sysinfo_index',
                'milexContent' => 'sysinfo',
                'route'         => $this->generateUrl('milex_sysinfo_index'),
            ],
        ]);
    }
}
