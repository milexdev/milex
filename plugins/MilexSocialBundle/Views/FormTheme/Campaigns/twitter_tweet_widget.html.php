<?php

/*
 * @copyright   2016 Milex, Inc. All rights reserved
 * @author      Milex, Inc
 *
 * @link        https://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
?>

<div id="compose-tweet">
    <div id="character-count" class="text-right small">
        <?php echo $view['translator']->trans('milex.social.twitter.tweet.count'); ?>
        <span></span>
    </div>
    <?php echo $view['form']->row($form['tweet_text']); ?>

    <div class="row">
        <div id="handle" class="col-md-2">
            <label class="control-label">
                <?php echo $view['translator']->trans('milex.social.twitter.tweet.handle'); ?>
            </label>
            <?php echo $view['form']->row($form['handle']); ?>
        </div>

        <div id="asset" class="col-md-5">
            <?php echo $view['form']->row($form['asset_link']); ?>
        </div>

        <div id="page" class="col-md-5">
            <?php echo $view['form']->row($form['page_link']); ?>
        </div>
    </div>
</div>
<?php echo $view['assets']->includeScript('plugins/MilexSocialBundle/Assets/js/social.js', 'composeSocialWatcher', 'composeSocialWatcher'); ?>