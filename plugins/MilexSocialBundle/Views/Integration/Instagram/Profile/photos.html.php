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

<div class="container-fluid">
    <div class="img-grid row">
        <?php $count = 0; ?>
        <?php foreach ($activity as $a): ?>
            <?php if ($count > 0 && 0 == $count % 3): echo '</div><div class="row">'; endif; ?>
            <div class="col-xs-4 social-image">
                <a href="javascript: void(0);" onclick="Milex.showSocialMediaImageModal('<?php echo $a['url']; ?>');">
                    <img class="img-responsive img-thumbnail" src="<?php echo $a['url']; ?>" />
                </a>
            </div>
            <?php ++$count; ?>
        <?php endforeach; ?>
    </div>
</div>