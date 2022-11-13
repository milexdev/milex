<?php

namespace Milex\CategoryBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Milex\CategoryBundle\Model\CategoryModel;
use Milex\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Router;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class CategoryListType.
 */
class CategoryListType extends AbstractType
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var CategoryModel
     */
    private $model;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var Router
     */
    private $router;

    /**
     * CategoryListType constructor.
     */
    public function __construct(EntityManager $em, TranslatorInterface $translator, CategoryModel $model, Router $router)
    {
        $this->em         = $em;
        $this->translator = $translator;
        $this->model      = $model;
        $this->router     = $router;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (true === $options['return_entity']) {
            $transformer = new IdToEntityModelTransformer($this->em, 'MilexCategoryBundle:Category', 'id');
            $builder->addModelTransformer($transformer);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => function (Options $options) {
                $createNew = $this->translator->trans('milex.category.createnew');
                $categories = $this->model->getLookupResults($options['bundle'], '', 0);
                $choices = [];
                foreach ($categories as $l) {
                    $choices[$l['title']] = $l['id'];
                }
                $choices[$createNew] = 'new';

                return $choices;
            },
            'label'             => 'milex.core.category',
            'label_attr'        => ['class' => 'control-label'],
            'multiple'          => false,
            'placeholder'       => 'milex.core.form.uncategorized',
            'attr'              => function (Options $options) {
                $modalHeader = $this->translator->trans('milex.category.header.new');
                $newUrl = $this->router->generate('milex_category_action', [
                    'objectAction' => 'new',
                    'bundle'       => $options['bundle'],
                    'inForm'       => 1,
                ]);

                return [
                    'class'    => 'form-control category-select',
                    'onchange' => "Milex.loadAjaxModalBySelectValue(this, 'new', '{$newUrl}', '{$modalHeader}');",
                ];
            },
            'required'      => false,
            'return_entity' => true,
        ]);

        $resolver->setRequired(['bundle']);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'category';
    }

    /**
     * @return string
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
