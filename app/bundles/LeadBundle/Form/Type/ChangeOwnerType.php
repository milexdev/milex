<?php

namespace Milex\LeadBundle\Form\Type;

use Milex\CoreBundle\Form\Type\FormButtonsType;
use Milex\UserBundle\Model\UserModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ChangeOwnerType extends AbstractType
{
    /**
     * @var UserModel
     */
    private $userModel;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'owner',
            ChoiceType::class,
            [
                'label'             => 'milex.lead.batch.add_to',
                'multiple'          => false,
                'choices'           => $this->userModel->getOwnerListChoices(),
                'required'          => true,
                'label_attr'        => ['class' => 'control-label'],
                'attr'              => ['class' => 'form-control'],
            ]
        );

        $builder->add(
          'buttons',
          FormButtonsType::class
        );
    }
}
