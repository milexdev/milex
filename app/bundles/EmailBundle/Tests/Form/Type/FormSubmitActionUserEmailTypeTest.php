<?php

declare(strict_types=1);

namespace Milex\EmailBundle\Tests\Form\Type;

use Doctrine\ORM\EntityManager;
use Milex\CoreBundle\Helper\CoreParametersHelper;
use Milex\EmailBundle\Form\Type\EmailSendType;
use Milex\EmailBundle\Form\Type\EmailType;
use Milex\EmailBundle\Form\Type\FormSubmitActionUserEmailType;
use Milex\StageBundle\Model\StageModel;
use Milex\UserBundle\Form\Type\UserListType;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class FormSubmitActionUserEmailTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject|TranslatorInterface
     */
    private $translator;

    /**
     * @var MockObject|EntityManager
     */
    private $entityManager;

    /**
     * @var MockObject|StageModel
     */
    private $stageModel;

    /**
     * @var MockObject|FormBuilderInterface
     */
    private $formBuilder;

    /**
     * @var EmailType
     */
    private $form;

    /**
     * @var CoreParametersHelper|MockObject
     */
    private $coreParametersHelper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formBuilder          = $this->createMock(FormBuilderInterface::class);
        $this->coreParametersHelper = $this->createMock(CoreParametersHelper::class);
        $this->form                 = new FormSubmitActionUserEmailType();
        $this->formBuilder->method('create')->willReturnSelf();
    }

    public function testBuildForm(): void
    {
        $options = [];

        $this->formBuilder->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                [
                    'useremail',
                    EmailSendType::class,
                    [
                        'label' => 'milex.email.emails',
                        'attr'  => [
                            'class'   => 'form-control',
                            'tooltip' => 'milex.email.choose.emails_descr',
                        ],
                        'update_select' => 'formaction_properties_useremail_email',
                    ],
                ],
                [
                    'user_id',
                    UserListType::class,
                    [
                        'label'      => 'milex.email.form.users',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => [
                            'class'   => 'form-control',
                            'tooltip' => 'milex.core.help.autocomplete',
                        ],
                        'required'    => true,
                        'constraints' => new NotBlank(
                            [
                                'message' => 'milex.core.value.required',
                            ]
                        ),
                    ],
                ]
            );

        $this->form->buildForm($this->formBuilder, $options);
    }
}
