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

<?php if (!empty($showMore)): ?>
<a href="<?php echo $view['router']->url('milex_role_index', ['filter-user' => $searchString]); ?>" data-toggle="ajax">
    <span><?php echo $view['translator']->trans('milex.core.search.more', ['%count%' => $remaining]); ?></span>
</a>
<?php else: ?>
<?php if ($canEdit): ?>
<a href="<?php echo $view['router']->url('milex_role_action', ['objectAction' => 'edit', 'objectId' => $role->getId()]); ?>" data-toggle="ajax">
    <?php echo $role->getName(true); ?>
</a>
<?php else: ?>
    <?php echo $role->getName(true); ?>
<?php endif; ?>
<?php endif; ?>