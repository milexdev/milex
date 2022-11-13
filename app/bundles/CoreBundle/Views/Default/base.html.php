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
<!DOCTYPE html>
<html>
    <?php echo $view->render('MilexCoreBundle:Default:head.html.php'); ?>
    <body class="header-fixed">
        <!-- start: app-wrapper -->
        <section id="app-wrapper">
            <?php $view['assets']->outputScripts('bodyOpen'); ?>

            <!-- start: app-sidebar(left) -->
            <aside class="app-sidebar sidebar-left">
                <?php echo $view->render('MilexCoreBundle:LeftPanel:index.html.php'); ?>
            </aside>
            <!--/ end: app-sidebar(left) -->

            <!-- start: app-sidebar(right) -->
            <aside class="app-sidebar sidebar-right">
                <?php echo $view->render('MilexCoreBundle:RightPanel:index.html.php'); ?>
            </aside>
            <!--/ end: app-sidebar(right) -->

            <!-- start: app-header -->
            <header id="app-header" class="navbar">
               <?php echo $view->render('MilexCoreBundle:Default:navbar.html.php'); ?>

               <?php echo $view->render('MilexCoreBundle:Notification:flashes.html.php'); ?>
            </header>
            <!--/ end: app-header -->

            <!-- start: app-footer(need to put on top of #app-content)-->
            <footer id="app-footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-6 text-muted"><?php echo $view['translator']->trans('milex.core.copyright', ['%date%' => date('Y')]); ?></div>
                        <div class="col-xs-6 text-muted text-right small">v<?php
                            /** @var \Milex\CoreBundle\Templating\Helper\VersionHelper $version */
                            $version = $view['version'];
                            echo $version->getVersion(); ?>
                        </div>
                    </div>
                </div>
            </footer>
            <!--/ end: app-footer -->

            <!-- start: app-content -->
            <section id="app-content">
                <?php $view['slots']->output('_content'); ?>
            </section>
            <!--/ end: app-content -->

        </section>
        <!--/ end: app-wrapper -->

        <script>
            Milex.onPageLoad('body');
            <?php if ('dev' === $app->getEnvironment()): ?>
            mQuery( document ).ajaxComplete(function(event, XMLHttpRequest, ajaxOption){
                if(XMLHttpRequest.responseJSON && typeof XMLHttpRequest.responseJSON.ignore_wdt == 'undefined' && XMLHttpRequest.getResponseHeader('x-debug-token')) {
                    if (mQuery('[class*="sf-tool"]').length) {
                        mQuery('[class*="sf-tool"]').remove();
                    }

                    mQuery.get(milexBaseUrl + '_wdt/'+XMLHttpRequest.getResponseHeader('x-debug-token'),function(data){
                        mQuery('body').append('<div class="sf-toolbar-reload">'+data+'</div>');
                    });
                }
            });
            <?php endif; ?>
        </script>
        <?php $view['assets']->outputScripts('bodyClose'); ?>
        <?php echo $view->render('MilexCoreBundle:Helper:modal.html.php', [
            'id'            => 'MilexSharedModal',
            'footerButtons' => true,
        ]); ?>
    </body>
</html>
