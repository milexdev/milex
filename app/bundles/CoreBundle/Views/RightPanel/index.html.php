<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$view['canvas']->renderCanvasContent($view);
$canvasContent = $view['canvas']->getContent();
?>

<div class="offcanvas-container" data-toggle="offcanvas" data-options='{"openerClass":"offcanvas-opener", "closerClass":"offcanvas-closer"}'>
    <!-- START Wrapper -->
    <div class="offcanvas-wrapper">
        <?php echo $view->render('MilexCoreBundle:RightPanel:left.html.php', ['canvasContent' => $canvasContent['left']]); ?>
        <?php echo $view->render('MilexCoreBundle:RightPanel:main.html.php', [
            'canvasContent' => $canvasContent['main'],
            'hasRight'      => !empty($canvasContent['right']),
            'hasLeft'       => !empty($canvasContent['left']),
        ]); ?>
        <?php echo $view->render('MilexCoreBundle:RightPanel:right.html.php', ['canvasContent' => $canvasContent['right']]); ?>
    </div>
    <!--/ END Wrapper -->
</div>