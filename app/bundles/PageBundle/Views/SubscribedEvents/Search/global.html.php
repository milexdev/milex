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
<a href="<?php echo $view['router']->url('milex_page_index', ['search' => $searchString]); ?>" data-toggle="ajax">
    <span><?php echo $view['translator']->trans('milex.core.search.more', ['%count%' => $remaining]); ?></span>
</a>
<?php else: ?>
<a href="<?php echo $view['router']->url('milex_page_action', ['objectAction' => 'view', 'objectId' => $page->getId()]); ?>" data-toggle="ajax">
    <?php echo $page->getTitle(); ?>
    <span class="label label-default pull-right" data-toggle="tooltip" title="<?php echo $view['translator']->trans('milex.page.hits'); ?>" data-placement="left">
        <?php echo $page->getHits(); ?>
    </span>
</a>
<?php endif; ?>