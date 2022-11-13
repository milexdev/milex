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
$view['slots']->set('headerTitle', $view['translator']->trans('milex.campaign.campaigns'));

$view['slots']->set(
    'actions',
    $view->render(
        'MilexCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions['campaign:campaigns:create'],
            ],
            'routeBase' => 'campaign',
        ]
    )
);
?>

<div class="panel panel-default bdr-t-wdh-0">
	<?php echo $view->render('MilexCoreBundle:Helper:list_toolbar.html.php', [
        'searchValue' => $searchValue,
        'searchHelp'  => 'milex.core.help.searchcommands',
        'action'      => $currentRoute,
        'filters'     => $filters,
    ]); ?>

    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>