<?php

namespace MilexPlugin\MilexEmailMarketingBundle\Form\Type;

use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Milex\CoreBundle\Helper\CoreParametersHelper;
use Milex\PluginBundle\Form\Type\FieldsType;
use Milex\PluginBundle\Helper\IntegrationHelper;
use Milex\PluginBundle\Model\PluginModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MailchimpType.
 */
class MailchimpType extends AbstractType
{
    /**
     * @var IntegrationHelper
     */
    private $integrationHelper;

    /** @var PluginModel */
    private $pluginModel;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CoreParametersHelper
     */
    protected $coreParametersHelper;

    public function __construct(IntegrationHelper $integrationHelper, PluginModel $pluginModel, Session $session, CoreParametersHelper $coreParametersHelper)
    {
        $this->integrationHelper    = $integrationHelper;
        $this->pluginModel          = $pluginModel;
        $this->session              = $session;
        $this->coreParametersHelper = $coreParametersHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \MilexPlugin\MilexEmailMarketingBundle\Integration\MailchimpIntegration $mailchimp */
        $mailchimp = $this->integrationHelper->getIntegrationObject('Mailchimp');

        $api = $mailchimp->getApiHelper();
        try {
            $lists   = $api->getLists();
            $choices = [];
            if (!empty($lists)) {
                if ($lists['total_items']) {
                    foreach ($lists['lists'] as $list) {
                        $choices[$list['id']] = $list['name'];
                    }
                }

                asort($choices);
            }
        } catch (\Exception $e) {
            $choices = [];
            $error   = $e->getMessage();
        }

        $builder->add('list', ChoiceType::class, [
            'choices'           => array_flip($choices), // Choice type expects labels as keys
            'label'             => 'milex.emailmarketing.list',
            'required'          => false,
            'attr'              => [
                'tooltip'  => 'milex.emailmarketing.list.tooltip',
                'onchange' => 'Milex.getIntegrationLeadFields(\'Mailchimp\', this, {"list": this.value});',
            ],
        ]);

        $builder->add('doubleOptin', YesNoButtonGroupType::class, [
            'label' => 'milex.mailchimp.double_optin',
            'data'  => (!isset($options['data']['doubleOptin'])) ? true : $options['data']['doubleOptin'],
        ]);

        $builder->add('sendWelcome', YesNoButtonGroupType::class, [
            'label' => 'milex.emailmarketing.send_welcome',
            'data'  => (!isset($options['data']['sendWelcome'])) ? true : $options['data']['sendWelcome'],
        ]);

        if (!empty($error)) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($error) {
                $form = $event->getForm();

                if ($error) {
                    $form['list']->addError(new FormError($error));
                }
            });
        }

        if (isset($options['form_area']) && 'integration' == $options['form_area']) {
            $leadFields = $this->pluginModel->getLeadFields();

            $formModifier = function (FormInterface $form, $data) use ($mailchimp, $leadFields) {
                $integrationName = $mailchimp->getName();
                $session         = $this->session;
                $limit           = $session->get(
                    'milex.plugin.'.$integrationName.'.lead.limit',
                    $this->coreParametersHelper->get('default_pagelimit')
                );
                $page     = $session->get('milex.plugin.'.$integrationName.'.lead.page', 1);
                $settings = [
                    'silence_exceptions' => false,
                    'feature_settings'   => [
                        'list_settings' => $data,
                    ],
                    'ignore_field_cache' => (1 == $page && 'POST' !== $_SERVER['REQUEST_METHOD']) ? true : false,
                ];
                try {
                    $fields = $mailchimp->getFormLeadFields($settings);

                    if (!is_array($fields)) {
                        $fields = [];
                    }
                    $error = '';
                } catch (\Exception $e) {
                    $fields = [];
                    $error  = $e->getMessage();
                    $page   = 1;
                }

                list($specialInstructions) = $mailchimp->getFormNotes('leadfield_match');
                $form->add('leadFields', FieldsType::class, [
                    'label'                => 'milex.integration.leadfield_matches',
                    'required'             => true,
                    'milex_fields'        => $leadFields,
                    'integration'          => $mailchimp->getName(),
                    'integration_object'   => $mailchimp,
                    'limit'                => $limit,
                    'page'                 => $page,
                    'data'                 => $data,
                    'integration_fields'   => $fields,
                    'special_instructions' => $specialInstructions,
                    'mapped'               => true,
                    'error_bubbling'       => false,
                ]);

                if ($error) {
                    $form->addError(new FormError($error));
                }
            };

            $builder->addEventListener(FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($formModifier) {
                    $data = $event->getData();
                    if (isset($data['leadFields']['leadFields'])) {
                        $data['leadFields'] = $data['leadFields']['leadFields'];
                    }
                    $formModifier($event->getForm(), $data);
                }
            );

            $builder->addEventListener(FormEvents::PRE_SUBMIT,
                function (FormEvent $event) use ($formModifier) {
                    $data = $event->getData();
                    if (isset($data['leadFields']['leadFields'])) {
                        $data['leadFields'] = $data['leadFields']['leadFields'];
                    }
                    $formModifier($event->getForm(), $data);
                }
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['form_area']);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'emailmarketing_mailchimp';
    }
}
