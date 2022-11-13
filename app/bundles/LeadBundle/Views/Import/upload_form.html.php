<?php

/*
 * @copyright   2014 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

$view['slots']->set(
    'actions',
    $view->render(
        'MilexCoreBundle:Helper:page_actions.html.php',
        [
            'routeBase'       => 'import',
            'langVar'         => 'lead.import',
            'templateButtons' => [
                'close' => true,
            ],
            'routeVars' => [
                'close' => [
                    'object' => $app->getRequest()->get('object', 'contacts'),
                ],
            ],
        ]
    )
);

?>
<div class="row">
    <div class="col-sm-offset-3 col-sm-6">
        <div class="ml-lg mr-lg mt-md pa-lg">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title"><?php echo $view['translator']->trans('milex.lead.import.start.instructions'); ?></div>
                </div>
                <div class="panel-body">
                    <?php echo $view['form']->start($form); ?>
                    <div class="input-group well mt-lg">
                        <?php echo $view['form']->widget($form['file']); ?>
                        <span class="input-group-btn">
                            <?php echo $view['form']->widget($form['start']); ?>
                        </span>
                    </div>

                    <div class="row">
                        <div class="col-xs-3">
                            <?php echo $view['form']->label($form['batchlimit']); ?>
                            <?php echo $view['form']->widget($form['batchlimit']); ?>
                            <?php echo $view['form']->errors($form['batchlimit']); ?>
                        </div>

                        <div class="col-xs-3">
                            <?php echo $view['form']->label($form['delimiter']); ?>
                            <?php echo $view['form']->widget($form['delimiter']); ?>
                            <?php echo $view['form']->errors($form['delimiter']); ?>
                        </div>

                        <div class="col-xs-3">
                            <?php echo $view['form']->label($form['enclosure']); ?>
                            <?php echo $view['form']->widget($form['enclosure']); ?>
                            <?php echo $view['form']->errors($form['enclosure']); ?>
                        </div>

                        <div class="col-xs-3">
                            <?php echo $view['form']->label($form['escape']); ?>
                            <?php echo $view['form']->widget($form['escape']); ?>
                            <?php echo $view['form']->errors($form['escape']); ?>
                        </div>
                    </div>
                    <?php echo $view['form']->end($form); ?>
                </div>
            </div>
        </div>
    </div>
</div>
