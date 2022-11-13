<?php

if ($dnc && \Milex\LeadBundle\Entity\DoNotContact::BOUNCED === $dnc->getReason()) {
    echo '<div class="alert alert-warning">'.$view['translator']->trans('milex.lead.do.not.contact_bounced').': '.$dnc->getComments().'</div>';
} else {
    echo $view['form']->start($form);

    echo $view['form']->row($form['fromname']);
    echo $view['form']->row($form['from']);
    echo $view['form']->row($form['subject']);
    echo $view['form']->row($form['body']);
    echo $view['form']->row($form['templates']);

    echo $view['form']->end($form);
}
