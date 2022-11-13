<?php

namespace Milex\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class DashboardLeadsInTimeWidgetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'flag',
            ChoiceType::class,
            [
                'label'             => 'milex.lead.list.filter',
                'choices'           => [
                    'milex.lead.show.all'                               => '',
                    'milex.lead.show.identified'                        => 'identified',
                    'milex.lead.show.anonymous'                         => 'anonymous',
                    'milex.lead.show.identified.vs.anonymous'           => 'identifiedVsAnonymous',
                    'milex.lead.show.top'                               => 'top',
                    'milex.lead.show.top.leads.identified.vs.anonymous' => 'topIdentifiedVsAnonymous',
                ],
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'empty_data' => '',
                'required'   => false,
            ]
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'lead_dashboard_leads_in_time_widget';
    }
}
