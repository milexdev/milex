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

$objectName = $view['translator']->trans($objectName);

$view['slots']->set('milexContent', 'leadImport');
$view['slots']->set('headerTitle', $view['translator']->trans('milex.lead.import.leads', ['%object%' => $objectName]));

?>
<?php if (isset($step) && \Milex\LeadBundle\Controller\ImportController::STEP_UPLOAD_CSV === $step): ?>
<?php echo $view->render('MilexLeadBundle:Import:upload_form.html.php', ['form' => $form]); ?>
<?php else: ?>
<?php echo $view->render('MilexLeadBundle:Import:mapping_form.html.php', ['form' => $form]); ?>
<?php endif; ?>
