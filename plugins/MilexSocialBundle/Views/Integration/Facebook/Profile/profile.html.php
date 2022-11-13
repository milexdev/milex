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
<div class="media">
    <?php if (isset($profile['profileImage'])): ?>
    <div class="pull-left thumbnail">
        <img src="<?php echo $profile['profileImage']; ?>" width="100px" class="media-object img-rounded" />
    </div>
    <?php endif; ?>

    <div class="media-body">
        <h4 class="media-heading"><?php echo $profile['name']; ?></h4>
        <p class="text-muted"><a href="https://facebook.com/<?php echo $profile['profileHandle']; ?>" target="_blank"><?php echo $profile['profileHandle']; ?></a></p>
    </div>
</div>
