<?php

namespace Milex\ApiBundle\Form\Type;

use Milex\ApiBundle\Form\Validator\Constraints\OAuthCallback;
use Milex\CoreBundle\Form\DataTransformer as Transformers;
use Milex\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Milex\CoreBundle\Form\EventListener\FormExitSubscriber;
use Milex\CoreBundle\Form\Type\FormButtonsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ClientType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        RequestStack $requestStack,
        TranslatorInterface $translator,
        ValidatorInterface $validator,
        Session $session,
        RouterInterface $router
    ) {
        $this->translator   = $translator;
        $this->validator    = $validator;
        $this->requestStack = $requestStack;
        $this->session      = $session;
        $this->router       = $router;
    }

    /**
     * @return bool|mixed
     */
    private function getApiMode()
    {
        return $this->requestStack->getCurrentRequest()->get(
            'api_mode',
            $this->session->get('milex.client.filter.api_mode', 'oauth2')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $apiMode = $this->getApiMode();
        $builder->addEventSubscriber(new CleanFormSubscriber([]));
        $builder->addEventSubscriber(new FormExitSubscriber('api.client', $options));

        if (!$options['data']->getId()) {
            $builder->add(
                'api_mode',
                ChoiceType::class,
                [
                    'mapped'     => false,
                    'label'      => 'milex.api.client.form.auth_protocol',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'    => 'form-control',
                        'onchange' => 'Milex.refreshApiClientForm(\''.$this->router->generate('milex_client_action', ['objectAction' => 'new']).'\', this)',
                    ],
                    'choices' => [
                        'OAuth 2'    => 'oauth2',
                    ],
                    'required'          => false,
                    'placeholder'       => false,
                    'data'              => $apiMode,
                ]
            );
        }

        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'milex.core.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $arrayStringTransformer = new Transformers\ArrayStringTransformer();
        $builder->add(
            $builder->create(
                'redirectUris',
                TextType::class,
                [
                    'label'      => 'milex.api.client.redirecturis',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'   => 'form-control',
                        'tooltip' => 'milex.api.client.form.help.requesturis',
                    ],
                ]
            )
                ->addViewTransformer($arrayStringTransformer)
        );

        $builder->add(
            'publicId',
            TextType::class,
            [
                'label'      => 'milex.api.client.form.clientid',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'disabled'   => true,
                'required'   => false,
                'mapped'     => false,
                'data'       => $options['data']->getPublicId(),
            ]
        );

        $builder->add(
            'secret',
            TextType::class,
            [
                'label'      => 'milex.api.client.form.clientsecret',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'disabled'   => true,
                'required'   => false,
            ]
        );

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                if ($form->has('redirectUris')) {
                    foreach ($data->getRedirectUris() as $uri) {
                        $urlConstraint = new OAuthCallback();
                        $urlConstraint->message = $this->translator->trans(
                            'milex.api.client.redirecturl.invalid',
                            ['%url%' => $uri],
                            'validators'
                        );

                        $errors = $this->validator->validate($uri, $urlConstraint);

                        if (!empty($errors)) {
                            foreach ($errors as $error) {
                                $form['redirectUris']->addError(new FormError($error->getMessage()));
                            }
                        }
                    }
                }
            }
        );

        $builder->add('buttons', FormButtonsType::class);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $dataClass = 'Milex\ApiBundle\Entity\oAuth2\Client';
        $resolver->setDefaults(
            [
                'data_class' => $dataClass,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'client';
    }
}
