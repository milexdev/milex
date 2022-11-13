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
$view['slots']->set('milexContent', 'company');
$view['slots']->set('headerTitle', $view['translator']->trans('milex.companies.menu.root'));

$pageButtons = [];
if ($permissions['lead:leads:create']) {
    $pageButtons[] = [
        'attr' => [
            'href' => $view['router']->path('milex_import_action', ['object' => 'companies', 'objectAction' => 'new']),
        ],
        'iconClass' => 'fa fa-upload',
        'btnText'   => 'milex.lead.lead.import',
    ];

    $pageButtons[] = [
        'attr' => [
            'href' => $view['router']->path('milex_import_index', ['object' => 'companies']),
        ],
        'iconClass' => 'fa fa-history',
        'btnText'   => 'milex.lead.lead.import.index',
    ];
}

$view['slots']->set(
    'actions',
    $view->render(
        'MilexCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions['lead:leads:create'],
            ],
            'routeBase'     => 'company',
            'customButtons' => $pageButtons,
        ]
    )
);
?>

<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <?php echo $view->render(
        'MilexCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue' => $searchValue,
            'searchHelp'  => 'milex.core.help.searchcommands',
            'action'      => $currentRoute,
        ]
    ); ?>
    <div class="page-list">
        <?php $view['slots']->output('_content'); ?>
    </div>
</div>
