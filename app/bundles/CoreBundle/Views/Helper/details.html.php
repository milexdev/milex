<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>
<?php echo $view['content']->getCustomContent('details.top', $milexTemplateVars); ?>
<?php if (method_exists($entity, 'getCategory')): ?>
<tr>
    <td width="20%"><span class="fw-b textTitle"><?php echo $view['translator']->trans('milex.core.category'); ?></span></td>
    <td><?php echo is_object($entity->getCategory()) ? $entity->getCategory()->getTitle() : $view['translator']->trans('milex.core.form.uncategorized'); ?></td>
</tr>
<?php endif; ?>

<?php if (method_exists($entity, 'getCreatedByUser')): ?>
<tr>
    <td width="20%"><span class="fw-b textTitle"><?php echo $view['translator']->trans('milex.core.createdby'); ?></span></td>
    <td><?php echo $entity->getCreatedByUser(); ?></td>
</tr>
<tr>
    <td width="20%"><span class="fw-b textTitle"><?php echo $view['translator']->trans('milex.core.created'); ?></span></td>
    <td><?php echo $view['date']->toFull($entity->getDateAdded()); ?></td>
</tr>
<?php endif; ?>
<?php
if (method_exists($entity, 'getModifiedByUser')):
$modified = $entity->getModifiedByUser();
if ($modified):
    ?>
    <tr>
        <td width="20%"><span class="fw-b textTitle"><?php echo $view['translator']->trans('milex.core.modifiedby'); ?></span></td>
        <td><?php echo $entity->getModifiedByUser(); ?></td>
    </tr>
    <tr>
        <td width="20%"><span class="fw-b textTitle"><?php echo $view['translator']->trans('milex.core.modified'); ?></span></td>
        <td><?php echo $view['date']->toFull($entity->getDateModified()); ?></td>
    </tr>
<?php endif; ?>
<?php endif; ?>
<?php if (method_exists($entity, 'getPublishUp')): ?>
<tr>
    <td width="20%"><span class="fw-b textTitle"><?php echo $view['translator']->trans('milex.page.publish.up'); ?></span></td>
    <td><?php echo (!is_null($entity->getPublishUp())) ? $view['date']->toFull($entity->getPublishUp()) : $view['date']->toFull($entity->getDateAdded()); ?></td>
</tr>
<tr>
    <td width="20%"><span class="fw-b textTitle"><?php echo $view['translator']->trans('milex.page.publish.down'); ?></span></td>
    <td><?php echo (!is_null($entity->getPublishDown())) ? $view['date']->toFull($entity->getPublishDown()) : $view['translator']->trans('milex.core.never'); ?></td>
</tr>
<?php endif; ?>
<?php if (method_exists($entity, 'getId')): ?>
    <tr>
        <td width="20%"><span class="fw-b textTitle"><?php echo $view['translator']->trans('milex.core.id'); ?></span></td>
        <td><?php echo $entity->getId(); ?></td>
    </tr>
<?php endif; ?>
<?php echo $view['content']->getCustomContent('details.bottom', $milexTemplateVars); ?>