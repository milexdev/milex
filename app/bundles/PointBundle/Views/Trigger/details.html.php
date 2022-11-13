<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MilexCoreBundle:Default:content.html.php');
$view['slots']->set('milexContent', 'pointTrigger');
$view['slots']->set('headerTitle', $entity->getName());

$view['slots']->set('actions', $view->render('MilexCoreBundle:Helper:page_actions.html.php', [
    'item'            => $entity,
    'templateButtons' => [
        'edit'   => $permissions['point:triggers:edit'],
        'delete' => $permissions['point:triggers:delete'],
    ],
    'routeBase' => 'pointtrigger',
    'langVar'   => 'point.trigger',
]));
?>

<div class="scrollable trigger-details">
    <?php //@todo - output trigger details/actions?>
</div>