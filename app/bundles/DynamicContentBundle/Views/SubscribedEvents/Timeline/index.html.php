<?php

/*
 * @copyright   2016 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$data = $event['extra']['log']['metadata'];

if (isset($data['failed']) || !isset($data['timeline'])) {
    return;
}
?>

<dl class="dl-horizontal">
    <dt><?php echo $view['translator']->trans('milex.dynamicContent.timeline.content'); ?></dt>
    <dd><?php echo $view['translator']->trans($data['timeline']); ?></dd>
</dl>
