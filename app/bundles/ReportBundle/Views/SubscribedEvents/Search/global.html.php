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
<a href="<?php echo $view['router']->url('milex_report_index', ['search' => $searchString]); ?>" data-toggle="ajax">
    <span><?php echo $view['translator']->trans('milex.core.search.more', ['%count%' => $remaining]); ?></span>
</a>
<?php else: ?>
<a href="<?php echo $view['router']->url('milex_report_view', ['objectId' => $item->getId()]); ?>" data-toggle="ajax">
    <?php echo $item->getName(); ?>
</a>
<?php endif; ?>