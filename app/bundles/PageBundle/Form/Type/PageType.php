<?php

namespace Milex\PageBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Milex\CategoryBundle\Form\Type\CategoryListType;
use Milex\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use Milex\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Milex\CoreBundle\Form\EventListener\FormExitSubscriber;
use Milex\CoreBundle\Form\Type\FormButtonsType;
use Milex\CoreBundle\Form\Type\ThemeListType;
use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Milex\CoreBundle\Helper\ThemeHelperInterface;
use Milex\CoreBundle\Helper\UserHelper;
use Milex\CoreBundle\Security\Permissions\CorePermissions;
use Milex\PageBundle\Entity\Page;
use Milex\PageBundle\Model\PageModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PageType.
 */
class PageType extends AbstractType
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Milex\PageBundle\Model\PageModel
     */
    private $model;

    /**
     * @var \Milex\UserBundle\Model\UserModel
     */
    private $user;

    /**
     * @var bool
     */
    private $canViewOther = false;

    /**
     * @var ThemeHelperInterface
     */
    private $themeHelper;

    public function __construct(
        EntityManager $entityManager,
        PageModel $pageModel,
        CorePermissions $corePermissions,
        UserHelper $userHelper,
        ThemeHelperInterface $themeHelper
    ) {
        $this->em           = $entityManager;
        $this->model        = $pageModel;
        $this->canViewOther = $corePermissions->isGranted('page:pages:viewother');
        $this->user         = $userHelper->getUser();
        $this->themeHelper  = $themeHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['content' => 'html', 'customHtml' => 'html', 'redirectUrl' => 'url', 'headScript' => 'html', 'footerScript' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('page.page', $options));

        $builder->add(
            'title',
            TextType::class,
            [
                'label'      => 'milex.core.title',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $builder->add(
            'customHtml',
            TextareaType::class,
            [
                'label'    => 'milex.page.form.customhtml',
                'required' => false,
                'attr'     => [
                    'tooltip'              => 'milex.page.form.customhtml.help',
                    'class'                => 'form-control editor-builder-tokens builder-html',
                    'data-token-callback'  => 'page:getBuilderTokens',
                    'data-token-activator' => '{',
                    'rows'                 => '25',
                ],
            ]
        );

        $template = $options['data']->getTemplate() ?? 'blank';
        // If theme does not exist, set empty
        $template = $this->themeHelper->getCurrentTheme($template, 'page');

        $builder->add(
            'template',
            ThemeListType::class,
            [
                'feature' => 'page',
                'attr'    => [
                    'class'   => 'form-control not-chosen hidden',
                    'tooltip' => 'milex.page.form.template.help',
                ],
                'placeholder' => 'milex.core.none',
                'data'        => $template,
            ]
        );

        $builder->add('isPublished', YesNoButtonGroupType::class);

        $builder->add(
            'isPreferenceCenter',
            YesNoButtonGroupType::class,
            [
                'label' => 'milex.page.form.preference_center',
                'data'  => $options['data']->isPreferenceCenter() ? $options['data']->isPreferenceCenter() : false,
                'attr'  => [
                    'tooltip' => 'milex.page.form.preference_center.tooltip',
                ],
            ]
        );

        $builder->add(
            'noIndex',
            YesNoButtonGroupType::class,
            [
                'label' => 'milex.page.config.no_index',
                'data'  => $options['data']->getNoIndex() ? $options['data']->getNoIndex() : false,
            ]
        );

        $builder->add(
            'publishUp',
            DateTimeType::class,
            [
                'widget'     => 'single_text',
                'label'      => 'milex.core.form.publishup',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'datetime',
                ],
                'format'   => 'yyyy-MM-dd HH:mm',
                'required' => false,
            ]
        );

        $builder->add(
            'publishDown',
            DateTimeType::class,
            [
                'widget'     => 'single_text',
                'label'      => 'milex.core.form.publishdown',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'datetime',
                ],
                'format'   => 'yyyy-MM-dd HH:mm',
                'required' => false,
            ]
        );

        $builder->add('sessionId', HiddenType::class);

        //Custom field for redirect URL
        $this->model->getRepository()->setCurrentUser($this->user);

        $redirectUrlDataOptions = '';
        $pages                  = $this->model->getRepository()->getPageList('', 0, 0, $this->canViewOther, 'variant', [$options['data']->getId()]);
        foreach ($pages as $page) {
            $redirectUrlDataOptions .= "|{$page['alias']}";
        }

        $transformer = new IdToEntityModelTransformer($this->em, 'MilexPageBundle:Page');
        $builder->add(
            $builder->create(
                'variantParent',
                HiddenType::class
            )->addModelTransformer($transformer)
        );

        $builder->add(
            $builder->create(
                'translationParent',
                PageListType::class,
                [
                    'label'      => 'milex.core.form.translation_parent',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'   => 'form-control',
                        'tooltip' => 'milex.core.form.translation_parent.help',
                    ],
                    'required'    => false,
                    'multiple'    => false,
                    'placeholder' => 'milex.core.form.translation_parent.empty',
                    'top_level'   => 'translation',
                    'ignore_ids'  => [(int) $options['data']->getId()],
                ]
            )->addModelTransformer($transformer)
        );

        $formModifier = function (FormInterface $form, $isVariant) {
            if ($isVariant) {
                $form->add(
                    'variantSettings',
                    VariantType::class,
                    [
                        'label' => false,
                    ]
                );
            }
        };

        // Building the form
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $formModifier(
                    $event->getForm(),
                    $event->getData()->getVariantParent()
                );
            }
        );

        // After submit
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                if (isset($data['variantParent'])) {
                    $formModifier(
                        $event->getForm(),
                        $data['variantParent']
                    );
                }
            }
        );

        $builder->add(
            'metaDescription',
            TextareaType::class,
            [
                'label'      => 'milex.page.form.metadescription',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control', 'maxlength' => 160],
                'required'   => false,
            ]
        );

        $builder->add(
            'headScript',
            TextareaType::class,
            [
                'label'      => 'milex.page.form.headscript',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                    'rows'  => '8',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
            'footerScript',
            TextareaType::class,
            [
                'label'      => 'milex.page.form.footerscript',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                    'rows'  => '8',
                ],
                'required'   => false,
            ]
        );

        $builder->add(
          'redirectType',
          RedirectListType::class,
          [
              'feature' => 'page',
              'attr'    => [
                  'class'   => 'form-control',
                  'tooltip' => 'milex.page.form.redirecttype.help',
              ],
              'placeholder' => 'milex.page.form.redirecttype.none',
          ]
        );

        $builder->add(
            'redirectUrl',
            UrlType::class,
            [
                'required'   => true,
                'label'      => 'milex.page.form.redirecturl',
                'label_attr' => [
                    'class' => 'control-label',
                ],
                'attr' => [
                    'class'        => 'form-control',
                    'maxlength'    => 200,
                    'tooltip'      => 'milex.page.form.redirecturl.help',
                    'data-toggle'  => 'field-lookup',
                    'data-action'  => 'page:fieldList',
                    'data-target'  => 'redirectUrl',
                    'data-options' => $redirectUrlDataOptions,
                ],
            ]
        );

        $builder->add(
            'alias',
            TextType::class,
            [
                'label'      => 'milex.core.alias',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'milex.page.help.alias',
                ],
                'required' => false,
            ]
        );

        //add category
        $builder->add(
            'category',
            CategoryListType::class,
            [
                'bundle' => 'page',
            ]
        );

        $builder->add(
            'language',
            LocaleType::class,
            [
                'label'      => 'milex.core.language',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'milex.page.form.language.help',
                ],
                'required'   => true,
            ]
        );

        $builder->add('buttons', FormButtonsType::class, [
            'pre_extra_buttons' => [
                [
                    'name'  => 'builder',
                    'label' => 'milex.core.builder',
                    'attr'  => [
                        'class'   => 'btn btn-default btn-dnd btn-nospin btn-builder text-primary',
                        'icon'    => 'fa fa-cube',
                        'onclick' => "Milex.launchBuilder('page');",
                    ],
                ],
            ],
        ]);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'page';
    }
}
