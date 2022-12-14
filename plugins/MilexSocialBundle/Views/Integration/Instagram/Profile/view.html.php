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

<div class="panel-toolbar np">
    <ul class="nav nav-tabs pr-md pl-md">
        <li class="active">
            <a href="#InstagramProfile" role="tab" data-toggle="tab">
                <?php echo $view['translator']->trans('milex.lead.lead.social.profile'); ?>
            </a>
        </li>
        <li>
            <a href="#InstagramPhotos" role="tab" data-toggle="tab">
                <?php echo $view['translator']->trans('milex.lead.lead.social.photos'); ?>
            </a>
        </li>
    </ul>
</div>
<div class="np panel-body tab-content">
    <div class="pa-20 tab-pane active" id="InstagramProfile">
        <?php echo $view->render('MilexSocialBundle:Integration/Instagram/Profile:profile.html.php', [
            'profile' => $details['profile'],
        ]); ?>
    </div>
    <div class="pa-20 tab-pane" id="InstagramPhotos">
        <?php echo $view->render('MilexSocialBundle:Integration/Instagram/Profile:photos.html.php', [
            'activity' => (!empty($details['activity']['photos'])) ? $details['activity']['photos'] : [],
        ]); ?>
    </div>
</div>