<?php

namespace Milex\FormBundle\Event;

use Milex\CoreBundle\Event\CommonEvent;
use Milex\FormBundle\Entity\Form;

/**
 * Class FormEvent.
 */
class FormEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Form &$form, $isNew = false)
    {
        $this->entity = &$form;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Form entity.
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->entity;
    }

    /**
     * Sets the Form entity.
     */
    public function setForm(Form $form)
    {
        $this->entity = $form;
    }
}
