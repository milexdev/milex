<?php

/*
 * @copyright   2016 Milex Contributors. All rights reserved
 * @author      Milex, Inc.
 *
 * @link        https://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

/** @var \Milex\ChannelBundle\Entity\Message $item */
$messageChannels = $item->getChannels();
$channels        = [];
if ($messageChannels) {
    foreach ($messageChannels as $channelName => $channel) {
        if (!$channel->isEnabled()) {
            continue;
        }

        $channels[] = $view['translator']->hasId('milex.channel.'.$channelName)
            ? $view['translator']->trans('milex.channel.'.$channelName)
            : ucfirst(
                $channelName
            );
    }
}
?>

<td class="visible-md visible-lg">
    <?php foreach ($channels as $channel): ?>
    <span class="label label-default"><?php echo $channel; ?></span>
    <?php endforeach; ?>
</td>