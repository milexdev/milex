<?php

namespace Milex\DynamicContentBundle\Form\Type;

use DeviceDetector\Parser\Device\AbstractDeviceParser as DeviceParser;
use DeviceDetector\Parser\OperatingSystem;
use Doctrine\ORM\EntityManager;
use Milex\CategoryBundle\Form\Type\CategoryListType;
use Milex\CoreBundle\Form\DataTransformer\EmojiToShortTransformer;
use Milex\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use Milex\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Milex\CoreBundle\Form\EventListener\FormExitSubscriber;
use Milex\CoreBundle\Form\Type\FormButtonsType;
use Milex\CoreBundle\Form\Type\YesNoButtonGroupType;
use Milex\DynamicContentBundle\Entity\DynamicContent;
use Milex\EmailBundle\Form\Type\EmailUtmTagsType;
use Milex\LeadBundle\Form\DataTransformer\FieldFilterTransformer;
use Milex\LeadBundle\Helper\FormFieldHelper;
use Milex\LeadBundle\Model\LeadModel;
use Milex\LeadBundle\Model\ListModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class DynamicContentType.
 */
class DynamicContentType extends AbstractType
{
    private $em;
    private $translator;
    private $fieldChoices;
    private $countryChoices;
    private $regionChoices;
    private $timezoneChoices;
    private $localeChoices;
    private $deviceTypesChoices;
    private $deviceBrandsChoices;
    private $deviceOsChoices;
    private $tagChoices = [];
    /**
     * @var LeadModel
     */
    private $leadModel;

    /**
     * DynamicContentType constructor.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(EntityManager $entityManager, ListModel $listModel, TranslatorInterface $translator, LeadModel $leadModel)
    {
        $this->em              = $entityManager;
        $this->translator      = $translator;
        $this->leadModel       = $leadModel;
        $this->fieldChoices    = $listModel->getChoiceFields();
        $this->timezoneChoices = FormFieldHelper::getTimezonesChoices();
        $this->countryChoices  = FormFieldHelper::getCountryChoices();
        $this->regionChoices   = FormFieldHelper::getRegionChoices();
        $this->localeChoices   = FormFieldHelper::getLocaleChoices();

        $this->filterFieldChoices();

        $tags = $leadModel->getTagList();
        foreach ($tags as $tag) {
            $this->tagChoices[$tag['value']] = $tag['label'];
        }

        $this->deviceTypesChoices  = array_combine(DeviceParser::getAvailableDeviceTypeNames(), DeviceParser::getAvailableDeviceTypeNames());
        $this->deviceBrandsChoices = DeviceParser::$deviceBrands;
        $this->deviceOsChoices     = array_combine(
            array_keys(OperatingSystem::getAvailableOperatingSystemFamilies()),
            array_keys(OperatingSystem::getAvailableOperatingSystemFamilies())
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['content' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('dynamicContent.dynamicContent', $options));

        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'milex.dynamicContent.form.internal.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $builder->add(
            'slotName',
            TextType::class,
            [
                'label'      => 'milex.dynamicContent.send.slot_name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'milex.dynamicContent.send.slot_name.tooltip',
                ],
            ]
        );

        $emojiTransformer = new EmojiToShortTransformer();
        $builder->add(
            $builder->create(
                'description',
                TextareaType::class,
                [
                    'label'      => 'milex.dynamicContent.description',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => ['class' => 'form-control'],
                    'required'   => false,
                ]
            )->addModelTransformer($emojiTransformer)
        );

        $builder->add('isPublished', YesNoButtonGroupType::class);

        $builder->add(
            'isCampaignBased',
            YesNoButtonGroupType::class,
            [
                'label' => 'milex.dwc.form.is_campaign_based',
                'data'  => (bool) $options['data']->isCampaignBased(),
                'attr'  => [
                    'tooltip'  => 'milex.dwc.form.is_campaign_based.tooltip',
                    'onchange' => 'Milex.toggleDwcFilters()',
                ],
            ]
        );

        $builder->add(
            'language',
            LocaleType::class,
            [
                'label'      => 'milex.core.language',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'required' => false,
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

        $builder->add(
            'content',
            TextareaType::class,
            [
                'label'      => 'milex.dynamicContent.form.content',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'tooltip'              => 'milex.dynamicContent.form.content.help',
                    'class'                => 'form-control editor editor-advanced editor-builder-tokens',
                    'data-token-callback'  => 'email:getBuilderTokens',
                    'data-token-activator' => '{',
                    'rows'                 => '15',
                ],
                'required' => false,
            ]
        );
        $builder->add(
            'utmTags',
            EmailUtmTagsType::class,
            [
                'label'      => 'milex.email.utm_tags',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'milex.email.utm_tags.tooltip',
                ],
                'required'   => false,
            ]
        );

        $transformer = new IdToEntityModelTransformer($this->em, 'MilexDynamicContentBundle:DynamicContent');
        $builder->add(
            $builder->create(
                'translationParent',
                DynamicContentListType::class,
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

        $builder->add(
            'category',
            CategoryListType::class,
            ['bundle' => 'dynamicContent']
        );

        if (!empty($options['update_select'])) {
            $builder->add(
                'buttons',
                FormButtonsType::class,
                ['apply_text' => false]
            );

            $builder->add(
                'updateSelect',
                HiddenType::class,
                [
                    'data'   => $options['update_select'],
                    'mapped' => false,
                ]
            );
        } else {
            $builder->add(
                'buttons',
                FormButtonsType::class
            );
        }

        $filterModalTransformer = new FieldFilterTransformer($this->translator);
        $builder->add(
            $builder->create(
                'filters',
                CollectionType::class,
                [
                    'entry_type'    => DwcEntryFiltersType::class,
                    'entry_options' => [
                        'countries'    => $this->countryChoices,
                        'regions'      => $this->regionChoices,
                        'timezones'    => $this->timezoneChoices,
                        'locales'      => $this->localeChoices,
                        'fields'       => $this->fieldChoices,
                        'deviceTypes'  => $this->deviceTypesChoices,
                        'deviceBrands' => $this->deviceBrandsChoices,
                        'deviceOs'     => $this->deviceOsChoices,
                        'tags'         => $this->tagChoices,
                    ],
                    'error_bubbling' => false,
                    'mapped'         => true,
                    'allow_add'      => true,
                    'allow_delete'   => true,
                ]
            )->addModelTransformer($filterModalTransformer)
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                // delete default prototype values
                $data = $event->getData();
                unset($data['filters']['__name__']);
                $event->setData($data);
            }
        );
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'     => DynamicContent::class,
            'label'          => false,
            'error_bubbling' => false,
        ]);

        $resolver->setDefined(['update_select']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['fields']       = $this->fieldChoices;
        $view->vars['countries']    = $this->countryChoices;
        $view->vars['regions']      = $this->regionChoices;
        $view->vars['timezones']    = $this->timezoneChoices;
        $view->vars['deviceTypes']  = $this->deviceTypesChoices;
        $view->vars['deviceBrands'] = $this->deviceBrandsChoices;
        $view->vars['deviceOs']     = $this->deviceOsChoices;
        $view->vars['tags']         = $this->tagChoices;
        $view->vars['locales']      = $this->localeChoices;
    }

    private function filterFieldChoices()
    {
        unset($this->fieldChoices['company']);
        $customFields               = $this->leadModel->getRepository()->getCustomFieldList('lead');
        $this->fieldChoices['lead'] = array_filter($this->fieldChoices['lead'], function ($key) use ($customFields) {
            return in_array($key, array_merge(array_keys($customFields[0]), ['date_added', 'date_modified', 'device_brand', 'device_model', 'device_os', 'device_type', 'tags']), true);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'dwc';
    }
}
