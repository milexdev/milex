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

<div class="panel-body">
    <?php echo $view->render('MilexSocialBundle:Integration/Facebook/Profile:profile.html.php', [
        'lead'    => $lead,
        'profile' => $details['profile'],
    ]); ?>
</div>