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
<!-- start: loading bar -->
<div class="loading-bar">
    <?php echo $view['translator']->trans('milex.core.loading'); ?>
</div>
<!--/ end: loading bar -->

<!-- start: navbar nocollapse -->
<div class="navbar-nocollapse">
    <!-- start: left nav -->
    <ul class="nav navbar-nav navbar-left">
        <li class="hidden-xs" data-toggle="tooltip" data-placement="right" title="Minimize Sidebar">
            <a href="javascript:void(0)" data-toggle="minimize" class="sidebar-minimizer"><span class="arrow fs-14"></span></a>
        </li>
        <li class="visible-xs">
            <a href="javascript: void(0);" data-toggle="sidebar" data-direction="ltr">
                <i class="fa fa-navicon fs-16"></i>
            </a>
        </li>
        <?php echo $view['actions']->render(new \Symfony\Component\HttpKernel\Controller\ControllerReference('MilexCoreBundle:Default:notifications')); ?>
        <?php echo $view['actions']->render(new \Symfony\Component\HttpKernel\Controller\ControllerReference('MilexCoreBundle:Default:globalSearch')); ?>
    </ul>
    <!--/ end: left nav -->

    <!-- start: right nav -->
    <ul class="nav navbar-nav navbar-right">
        <?php echo $view->render('MilexCoreBundle:Menu:profile.html.php'); ?>
        <li>
            <a href="javascript: void(0);" data-toggle="sidebar" data-direction="rtl">
                <i class="fa fa-cog fs-16"></i>
            </a>
        </li>
    </ul>
    <div class="navbar-toolbar pull-right mt-15 mr-10">
    <?php
    echo $view['buttons']->reset($app->getRequest(), \Milex\CoreBundle\Templating\Helper\ButtonHelper::LOCATION_NAVBAR)
        ->renderButtons();
    ?>
    </div>


    <!--/ end: right nav -->
</div>
<!--/ end: navbar nocollapse -->