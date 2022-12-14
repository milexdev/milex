<?php

declare(strict_types=1);

namespace Milex\IntegrationsBundle\Form\Type;

use Milex\LeadBundle\Model\LeadModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityListType extends AbstractType
{
    /**
     * @var LeadModel
     */
    private $leadModel;

    public function __construct(LeadModel $leadModel)
    {
        $this->leadModel = $leadModel;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choices'    => $this->leadModel->getEngagementTypes(),
                'label'      => 'milex.integration.feature.push_activity.included_events',
                'label_attr' => [
                    'class'       => 'control-label',
                    'tooltip'     => 'milex.integration.feature.push_activity.included_events.tooltip',
                ],
                'multiple'   => true,
                'required'   => false,
            ]
        );
    }

    /**
     * @return string|\Symfony\Component\Form\FormTypeInterface|null
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
