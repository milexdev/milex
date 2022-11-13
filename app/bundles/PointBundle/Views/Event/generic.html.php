<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$containerClass = (!empty($deleted)) ? ' bg-danger' : '';
?>

<div class="trigger-event-row <?php echo $containerClass; ?>" id="triggerEvent_<?php echo $id; ?>">
    <?php echo $view->render('MilexPointBundle:Event:actions.html.php', [
        'deleted'   => (!empty($deleted)) ? $deleted : false,
        'id'        => $id,
        'route'     => 'milex_pointtriggerevent_action',
        'sessionId' => $sessionId,
    ]); ?>
    <span class="trigger-event-label"><?php echo $event['name']; ?></span>
    <?php if (!empty($event['description'])): ?>
    <span class="trigger-event-descr"><?php echo $event['description']; ?></span>
    <?php endif; ?>
</div>