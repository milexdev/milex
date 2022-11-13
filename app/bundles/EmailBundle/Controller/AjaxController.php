<?php

namespace Milex\EmailBundle\Controller;

use Milex\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Milex\CoreBundle\Controller\AjaxLookupControllerTrait;
use Milex\CoreBundle\Controller\VariantAjaxControllerTrait;
use Milex\CoreBundle\Translation\Translator;
use Milex\EmailBundle\Helper\MailHelper;
use Milex\EmailBundle\Helper\PlainTextHelper;
use Milex\EmailBundle\Model\EmailModel;
use Milex\PageBundle\Form\Type\AbTestPropertiesType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends CommonAjaxController
{
    use VariantAjaxControllerTrait;
    use AjaxLookupControllerTrait;

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function getAbTestFormAction(Request $request)
    {
        return $this->getAbTestForm(
            $request,
            'email',
            AbTestPropertiesType::class,
            'email_abtest_settings',
            'emailform',
            'MilexEmailBundle:AbTest:form.html.php',
            ['MilexEmailBundle:AbTest:form.html.php', 'MilexEmailBundle:FormTheme\Email']
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function sendBatchAction(Request $request)
    {
        $dataArray = ['success' => 0];

        /** @var \Milex\EmailBundle\Model\EmailModel $model */
        $model    = $this->getModel('email');
        $objectId = $request->request->get('id', 0);
        $pending  = $request->request->get('pending', 0);
        $limit    = $request->request->get('batchlimit', 100);

        if ($objectId && $entity = $model->getEntity($objectId)) {
            $dataArray['success'] = 1;
            $session              = $this->container->get('session');
            $progress             = $session->get('milex.email.send.progress', [0, (int) $pending]);
            $stats                = $session->get('milex.email.send.stats', ['sent' => 0, 'failed' => 0, 'failedRecipients' => []]);
            $inProgress           = $session->get('milex.email.send.active', false);

            if ($pending && !$inProgress && $entity->isPublished()) {
                $session->set('milex.email.send.active', true);
                list($batchSentCount, $batchFailedCount, $batchFailedRecipients) = $model->sendEmailToLists($entity, null, $limit);

                $progress[0] += ($batchSentCount + $batchFailedCount);
                $stats['sent'] += $batchSentCount;
                $stats['failed'] += $batchFailedCount;

                foreach ($batchFailedRecipients as $emails) {
                    $stats['failedRecipients'] = $stats['failedRecipients'] + $emails;
                }

                $session->set('milex.email.send.progress', $progress);
                $session->set('milex.email.send.stats', $stats);
                $session->set('milex.email.send.active', false);
            }

            $dataArray['percent']  = ($progress[1]) ? ceil(($progress[0] / $progress[1]) * 100) : 100;
            $dataArray['progress'] = $progress;
            $dataArray['stats']    = $stats;
        }

        return $this->sendJsonResponse($dataArray);
    }

    /**
     * Called by parent::getBuilderTokensAction().
     *
     * @param $query
     *
     * @return array
     */
    protected function getBuilderTokens($query)
    {
        /** @var \Milex\EmailBundle\Model\EmailModel $model */
        $model = $this->getModel('email');

        return $model->getBuilderComponents(null, ['tokens'], $query, false);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function generatePlaintTextAction(Request $request)
    {
        $custom = $request->request->get('custom');
        $id     = $request->request->get('id');

        $parser = new PlainTextHelper(
            [
                'base_url' => $request->getSchemeAndHttpHost().$request->getBasePath(),
            ]
        );

        $dataArray = [
            'text' => $parser->setHtml($custom)->getText(),
        ];

        return $this->sendJsonResponse($dataArray);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function getAttachmentsSizeAction(Request $request)
    {
        $assets = $request->get('assets', [], true);
        $size   = 0;
        if ($assets) {
            /** @var \Milex\AssetBundle\Model\AssetModel $assetModel */
            $assetModel = $this->getModel('asset');
            $size       = $assetModel->getTotalFilesize($assets);
        }

        return $this->sendJsonResponse(['size' => $size]);
    }

    /**
     * Tests monitored email connection settings.
     *
     * @return JsonResponse
     */
    protected function testMonitoredEmailServerConnectionAction(Request $request)
    {
        $dataArray = ['success' => 0, 'message' => ''];

        if ($this->user->isAdmin()) {
            $settings = $request->request->all();

            if (empty($settings['password'])) {
                $existingMonitoredSettings = $this->coreParametersHelper->get('monitored_email');
                if (is_array($existingMonitoredSettings) && (!empty($existingMonitoredSettings[$settings['mailbox']]['password']))) {
                    $settings['password'] = $existingMonitoredSettings[$settings['mailbox']]['password'];
                }
            }

            /** @var \Milex\EmailBundle\MonitoredEmail\Mailbox $helper */
            $helper = $this->factory->getHelper('mailbox');

            try {
                $helper->setMailboxSettings($settings, false);
                $folders = $helper->getListingFolders('');
                if (!empty($folders)) {
                    $dataArray['folders'] = '';
                    foreach ($folders as $folder) {
                        $dataArray['folders'] .= "<option value=\"$folder\">$folder</option>\n";
                    }
                }
                $dataArray['success'] = 1;
                $dataArray['message'] = $this->translator->trans('milex.core.success');
            } catch (\Exception $e) {
                $dataArray['message'] = $this->translator->trans($e->getMessage());
            }
        }

        return $this->sendJsonResponse($dataArray);
    }

    /**
     * Tests mail transport settings.
     *
     * @return JsonResponse
     */
    protected function testEmailServerConnectionAction(Request $request)
    {
        $dataArray = ['success' => 0, 'message' => ''];
        $user      = $this->get('milex.helper.user')->getUser();

        if ($user->isAdmin()) {
            $settings = $request->request->all();

            $transport = $settings['transport'];

            switch ($transport) {
                case 'gmail':
                    $mailer = new \Swift_SmtpTransport('smtp.gmail.com', 465, 'ssl');
                    break;
                case 'smtp':
                    $mailer = new \Swift_SmtpTransport($settings['host'], $settings['port'], $settings['encryption']);
                    break;
                default:
                    if ($this->container->has($transport)) {
                        $mailer = $this->container->get($transport);

                        if ('milex.transport.amazon' == $transport) {
                            $amazonHost = $mailer->buildHost($settings['amazon_region'], $settings['amazon_other_region']);
                            $mailer->setHost($amazonHost, $settings['port']);
                        }

                        if ('milex.transport.amazon_api' == $transport) {
                            $mailer->setRegion($settings['amazon_region'], $settings['amazon_other_region']);
                        }
                    }
            }

            if (method_exists($mailer, 'setMilexFactory')) {
                $mailer->setMilexFactory($this->factory);
            }

            if (!empty($mailer)) {
                try {
                    if (method_exists($mailer, 'setApiKey')) {
                        if (empty($settings['api_key'])) {
                            $settings['api_key'] = $this->get('milex.helper.core_parameters')->get('mailer_api_key');
                        }
                        $mailer->setApiKey($settings['api_key']);
                    }
                } catch (\Exception $exception) {
                    // Transport had magic method defined and threw an exception
                }

                try {
                    if (is_callable([$mailer, 'setUsername']) && is_callable([$mailer, 'setPassword'])) {
                        if (empty($settings['password'])) {
                            $settings['password'] = $this->get('milex.helper.core_parameters')->get('mailer_password');
                        }
                        $mailer->setUsername($settings['user']);
                        $mailer->setPassword($settings['password']);
                    }
                } catch (\Exception $exception) {
                    // Transport had magic method defined and threw an exception
                }

                $logger = new \Swift_Plugins_Loggers_ArrayLogger();
                $mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));

                try {
                    $mailer->start();
                    $dataArray['success'] = 1;
                    $dataArray['message'] = $this->get('translator')->trans('milex.core.success');
                } catch (\Exception $e) {
                    $dataArray['message'] = $e->getMessage().'<br />'.$logger->dump();
                }
            }
        }

        return $this->sendJsonResponse($dataArray);
    }

    protected function sendTestEmailAction(Request $request)
    {
        /** @var MailHelper $mailer */
        $mailer = $this->get('milex.helper.mailer');
        /** @var Translator $translator */
        $translator = $this->get('translator');

        $mailer->setSubject($translator->trans('milex.email.config.mailer.transport.test_send.subject'));
        $mailer->setBody($translator->trans('milex.email.config.mailer.transport.test_send.body'));

        $user         = $this->get('milex.helper.user')->getUser();
        $userFullName = trim($user->getFirstName().' '.$user->getLastName());
        if (empty($userFullName)) {
            $userFullName = null;
        }
        $mailer->setTo([$user->getEmail() => $userFullName]);

        $success = 1;
        $message = $translator->trans('milex.core.success');
        if (!$mailer->send(true)) {
            $success   = 0;
            $errors    = $mailer->getErrors();
            unset($errors['failures']);
            $message = implode('; ', $errors);
        }

        return $this->sendJsonResponse(['success' => $success, 'message' => $message]);
    }

    protected function getEmailCountStatsAction(Request $request)
    {
        /** @var EmailModel $model */
        $model = $this->getModel('email');

        $id  = $request->get('id');
        $ids = $request->get('ids');

        // Support for legacy calls
        if (!$ids && $id) {
            $ids = [$id];
        }

        $data = [];
        foreach ($ids as $id) {
            if ($email = $model->getEntity($id)) {
                $pending = $model->getPendingLeads($email, null, true);
                $queued  = $model->getQueuedCounts($email);

                $data[] = [
                    'id'          => $id,
                    'pending'     => 'list' === $email->getEmailType() && $pending ? $this->translator->trans(
                        'milex.email.stat.leadcount',
                        ['%count%' => $pending]
                    ) : 0,
                    'queued'      => ($queued) ? $this->translator->trans('milex.email.stat.queued', ['%count%' => $queued]) : 0,
                    'sentCount'   => $this->translator->trans('milex.email.stat.sentcount', ['%count%' => $email->getSentCount(true)]),
                    'readCount'   => $this->translator->trans('milex.email.stat.readcount', ['%count%' => $email->getReadCount(true)]),
                    'readPercent' => $this->translator->trans('milex.email.stat.readpercent', ['%count%' => $email->getReadPercentage(true)]),
                ];
            }
        }

        // Support for legacy calls
        if ($request->get('id') && !empty($data[0])) {
            $data = $data[0];
        } else {
            $data = [
                'success' => 1,
                'stats'   => $data,
            ];
        }

        return new JsonResponse($data);
    }
}
