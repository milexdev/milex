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
if (!$view['slots']->get('milexContent')) {
    if (isset($milexContent)) {
        $view['slots']->set('milexContent', $milexContent);
    }
}

if (!$view['slots']->get('headerTitle')) {
    if (!isset($headerTitle)) {
        $headerTitle = 'Milex';
    }
    $view['slots']->set('headerTitle', $view['translator']->trans($headerTitle));
}

$view['slots']->set(
    'actions',
    $view->render(
        'MilexCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions[$permissionBase.':create'],
            ],
            'actionRoute'     => $actionRoute,
            'indexRoute'      => $indexRoute,
            'translationBase' => $translationBase,
        ]
    )
);
?>

<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <?php echo $view->render(
        'MilexCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue'      => $searchValue,
            'searchHelp'       => isset($searchHelp) ? $searchHelp : '',
            'action'           => $currentRoute,
            'actionRoute'      => $actionRoute,
            'indexRoute'       => $indexRoute,
            'translationBase'  => $translationBase,
            'preCustomButtons' => (isset($toolBarButtons)) ? $toolBarButtons : null,
            'templateButtons'  => [
                'delete' => $permissions[$permissionBase.':delete'],
            ],
            'filters' => (isset($filters)) ? $filters : [],
        ]
    ); ?>

    <div class="page-list">
        <?php echo $view['content']->getCustomContent('content.above', $milexTemplateVars); ?>
        <?php $view['slots']->output('_content'); ?>
        <?php echo $view['content']->getCustomContent('content.below', $milexTemplateVars); ?>
    </div>
</div>