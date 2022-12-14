<?php

/*
 * @copyright   2016 Milex, Inc. All rights reserved
 * @author      Milex, Inc
 *
 * @link        https://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
$view->extend('MilexCoreBundle:Default:content.html.php');

$view['slots']->set('milexContent', 'tweet');
echo $view['assets']->includeScript('plugins/MilexSocialBundle/Assets/js/social.js', 'composeSocialWatcher', 'composeSocialWatcher');
?>

<?php echo $view['form']->start($form); ?>
<?php echo $view['form']->row($form['name']); ?>
<?php echo $view['form']->row($form['text']); ?>
<div id="character-count" class="text-right small">
    <?php echo $view['translator']->trans('milex.social.twitter.tweet.count'); ?>
    <span></span>
</div>

<div class="row">
    <div class="col-md-4">
        <label class="control-label">
            <?php echo $view['translator']->trans('milex.social.twitter.tweet.handle'); ?>
        </label>
        <?php echo $view['form']->row($form['handle']); ?>
    </div>
    <div class="col-md-4">
        <?php echo $view['form']->row($form['asset']); ?>
    </div>
    <div class="col-md-4">
        <?php echo $view['form']->row($form['page']); ?>
    </div>
</div>

<?php echo $view['form']->row($form['description']); ?>
<?php echo $view['form']->row($form['category']); ?>
<?php echo $view['form']->end($form); ?>
