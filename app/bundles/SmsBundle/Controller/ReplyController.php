<?php

namespace Milex\SmsBundle\Controller;

use Milex\SmsBundle\Callback\HandlerContainer;
use Milex\SmsBundle\Exception\CallbackHandlerNotFound;
use Milex\SmsBundle\Helper\ReplyHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReplyController extends AbstractController
{
    /**
     * @var HandlerContainer
     */
    private $callbackHandler;

    /**
     * @var ReplyHelper
     */
    private $replyHelper;

    /**
     * ReplyController constructor.
     */
    public function __construct(HandlerContainer $callbackHandler, ReplyHelper $replyHelper)
    {
        $this->callbackHandler = $callbackHandler;
        $this->replyHelper     = $replyHelper;
    }

    /**
     * @param $transport
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function callbackAction(Request $request, $transport)
    {
        define('MILEX_NON_TRACKABLE_REQUEST', 1);

        try {
            $handler = $this->callbackHandler->getHandler($transport);
        } catch (CallbackHandlerNotFound $exception) {
            throw new NotFoundHttpException();
        }

        return $this->replyHelper->handleRequest($handler, $request);
    }
}
