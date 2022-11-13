<?php

namespace Milex\SmsBundle\Controller\Api;

use Milex\ApiBundle\Controller\CommonApiController;
use Milex\LeadBundle\Controller\LeadAccessTrait;
use Milex\SmsBundle\Model\SmsModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Class SmsApiController.
 */
class SmsApiController extends CommonApiController
{
    use LeadAccessTrait;

    /**
     * @var SmsModel
     */
    protected $model;

    /**
     * {@inheritdoc}
     */
    public function initialize(FilterControllerEvent $event)
    {
        $this->model           = $this->getModel('sms');
        $this->entityClass     = 'Milex\SmsBundle\Entity\Sms';
        $this->entityNameOne   = 'sms';
        $this->entityNameMulti = 'smses';

        parent::initialize($event);
    }

    /**
     * @param $id
     * @param $contactId
     *
     * @return JsonResponse|Response
     */
    public function sendAction($id, $contactId)
    {
        if (!$this->get('milex.sms.transport_chain')->getEnabledTransports()) {
            return new JsonResponse(json_encode(['error' => ['message' => 'SMS transport is disabled.', 'code' => Response::HTTP_EXPECTATION_FAILED]]));
        }

        $message = $this->model->getEntity((int) $id);

        if (is_null($message)) {
            return $this->notFound();
        }

        $contact = $this->checkLeadAccess($contactId, 'edit');

        if ($contact instanceof Response) {
            return $this->accessDenied();
        }

        $this->get('monolog.logger.milex')
             ->addDebug("Sending SMS #{$id} to contact #{$contactId}", ['originator' => 'api']);

        try {
            $response = $this->model->sendSms($message, $contact, ['channel' => 'api'])[$contact->getId()];
        } catch (\Exception $e) {
            $this->get('monolog.logger.milex')->addError($e->getMessage(), ['error' => (array) $e]);

            return new Response('Interval server error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $success = !empty($response['sent']);

        if (!$success) {
            $this->get('monolog.logger.milex')->addError('Failed to send SMS.', ['error' => $response['status']]);
        }

        $view = $this->view(
            [
                'success' => $success,
                'status'  => $this->get('translator')->trans($response['status']),
                'result'  => $response,
                'errors'  => $success ? [] : [['message' => $response['status']]],
            ],
            Response::HTTP_OK  //  200 - is legacy, we cannot change it yet
        );

        return $this->handleView($view);
    }
}
