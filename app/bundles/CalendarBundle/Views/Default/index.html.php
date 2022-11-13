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
$view['slots']->set('milexContent', 'calendar');
$view['slots']->set('headerTitle', $view['translator']->trans('milex.calendar.menu.index'));
?>

<div class="panel panel-default mnb-5 bdr-t-wdh-0">
	<div class="panel-body">
		<div id="calendar"></div>
	</div>
</div>

<?php echo $view->render('MilexCoreBundle:Helper:modal.html.php', [
    'id'            => 'CalendarEditModal',
    'footerButtons' => true,
]);
