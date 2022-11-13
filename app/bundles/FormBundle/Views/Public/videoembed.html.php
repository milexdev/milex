<div id="milex-video-form-embed">
    <form method="post" action="<?php echo $view['router']->url('milex_form_postresults', ['formId' => $form->getId()]); ?>">
        <?php
        /** @var \Milex\FormBundle\Entity\Field $f */
        foreach ($form->getFields() as $f):
            if ($f->isCustom()):
                if (!isset($fieldSettings[$f->getType()])):
                    continue;
                endif;
                $params = $fieldSettings[$f->getType()];
                $f->setCustomParameters($params);

                $template = $params['template'];
            else:
                $template = 'MilexFormBundle:Field:'.$f->getType().'.html.php';
            endif;

            echo $view->render($template, ['field' => $f->convertToArray(), 'id' => $f->getAlias(), 'formName' => $f->getForm()->generateFormName()]);
        endforeach;
        ?>
    </form>
</div>
