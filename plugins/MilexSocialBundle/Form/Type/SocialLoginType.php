<?php

namespace MilexPlugin\MilexSocialBundle\Form\Type;

use Milex\CoreBundle\Helper\CoreParametersHelper;
use Milex\FormBundle\Model\FormModel;
use Milex\PluginBundle\Helper\IntegrationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class FacebookLoginType.
 */
class SocialLoginType extends AbstractType
{
    /**
     * @var IntegrationHelper
     */
    private $helper;
    private $formModel;
    private $coreParametersHelper;

    /**
     * SocialLoginType constructor.
     */
    public function __construct(IntegrationHelper $helper, FormModel $form, CoreParametersHelper $coreParametersHelper)
    {
        $this->helper               = $helper;
        $this->formModel            = $form;
        $this->coreParametersHelper = $coreParametersHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $integrations       = '';
        $integrationObjects = $this->helper->getIntegrationObjects(null, 'login_button');
        foreach ($integrationObjects as $integrationObject) {
            if ($integrationObject->getIntegrationSettings()->isPublished()) {
                $model = $this->formModel;
                $integrations .= $integrationObject->getName().',';
                $integration = [
                    'integration' => $integrationObject->getName(),
                ];

                $builder->add(
                    'authUrl_'.$integrationObject->getName(),
                    HiddenType::class,
                    [
                        'data' => $model->buildUrl('milex_integration_auth_user', $integration, true, []),
                    ]
                );

                $builder->add(
                    'buttonImageUrl',
                    HiddenType::class,
                    [
                        'data' => $this->coreParametersHelper->get('site_url').'/'.$this->coreParametersHelper->get('image_path').'/',
                    ]
                );
            }
        }

        $builder->add(
            'integrations',
            HiddenType::class,
            [
                'data' => $integrations,
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sociallogin';
    }
}
