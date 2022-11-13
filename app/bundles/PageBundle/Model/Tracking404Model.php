<?php

namespace Milex\PageBundle\Model;

use Milex\CoreBundle\Helper\CoreParametersHelper;
use Milex\LeadBundle\Tracker\ContactTracker;
use Milex\PageBundle\Entity\Page;
use Milex\PageBundle\Entity\Redirect;
use Symfony\Component\HttpFoundation\Request;

class Tracking404Model
{
    /**
     * @var ContactTracker
     */
    private $contactTracker;

    /**
     * @var PageModel
     */
    private $pageModel;

    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    /**
     * Tracking404Model constructor.
     */
    public function __construct(
        CoreParametersHelper $coreParametersHelper,
        ContactTracker $contactTracker,
        PageModel $pageModel
    ) {
        $this->coreParametersHelper = $coreParametersHelper;
        $this->contactTracker       = $contactTracker;
        $this->pageModel            = $pageModel;
    }

    /**
     * @param Page|Redirect $entity
     *
     * @throws \Exception
     */
    public function hitPage($entity, Request $request): void
    {
        $this->pageModel->hitPage($entity, $request, 404);
    }

    /**
     * @return bool
     */
    public function isTrackable()
    {
        if (!$this->coreParametersHelper->get('do_not_track_404_anonymous')) {
            return true;
        }
        // already tracked and identified contact
        if ($lead = $this->contactTracker->getContactByTrackedDevice()) {
            if (!$lead->isAnonymous()) {
                return true;
            }
        }

        return false;
    }
}
