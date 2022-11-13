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

<div class="offcanvas-main" id="OffCanvasMain">
    <?php if ($canvasContent): ?>
    <?php echo $view->render('MilexCoreBundle:RightPanel:content.html.php', ['canvasContent' => $canvasContent, 'canvas' => 'Main', 'hasLeft' => $hasLeft, 'hasRight' => $hasRight]); ?>
    <?php endif; ?>
</div>
<!--/ Offcanvas Main -->
