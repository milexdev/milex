<?php

declare(strict_types=1);

namespace Milex\LeadBundle\Provider;

use Milex\LeadBundle\Event\FormAdjustmentEvent;
use Milex\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;

final class FormAdjustmentsProvider implements FormAdjustmentsProviderInterface
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param FormInterface<FormInterface> $form
     * @param mixed[]                      $fieldDetails
     *
     * @return FormInterface<FormInterface>
     */
    public function adjustForm(FormInterface $form, string $fieldAlias, string $fieldObject, string $operator, array $fieldDetails): FormInterface
    {
        $event = new FormAdjustmentEvent($form, $fieldAlias, $fieldObject, $operator, $fieldDetails);
        $this->dispatcher->dispatch(LeadEvents::ADJUST_FILTER_FORM_TYPE_FOR_FIELD, $event);

        return $event->getForm();
    }
}
