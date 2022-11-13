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
$view['slots']->set('milexContent', 'leadlist');
$view['slots']->set('headerTitle', $view['translator']->trans('milex.lead.list.header.index'));

$view['slots']->set(
    'actions',
    $view->render(
        'MilexCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => true, // this is intentional. Each user can segment leads
            ],
            'routeBase' => 'segment',
            'langVar'   => 'lead.list',
            'tooltip'   => 'milex.lead.lead.segment.add.help',
        ]
    )
);
?>

<div class="panel panel-default bdr-t-wdh-0">
    <?php echo $view->render(
        'MilexCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue' => $searchValue,
            'searchHelp'  => 'milex.lead.list.help.searchcommands',
            'action'      => $currentRoute,
            'filters'     => (isset($filters)) ? $filters : [],
        ]
    ); ?>
    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>
