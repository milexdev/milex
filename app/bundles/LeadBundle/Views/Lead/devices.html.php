<?php

/*
 * @copyright   2019 Milex Contributors. All rights reserved
 * @author      Milex
 *
 * @link        http://milex.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

use DeviceDetector\Parser\Device\AbstractDeviceParser;
use Milex\CoreBundle\Helper\Serializer;

?>

<table class="table table-bordered table-striped mb-0">
    <thead>
        <tr>
            <th class="timeline-icon"></th>
            <th><?php echo $view['translator']->trans('milex.lead.device.header'); ?></th>
            <th><?php echo $view['translator']->trans('milex.lead.device_os_name.header'); ?></th>
            <th><?php echo $view['translator']->trans('milex.lead.device_os_version.header'); ?></th>
            <th><?php echo $view['translator']->trans('milex.lead.device_browser.header'); ?></th>
            <th><?php echo $view['translator']->trans('milex.lead.device_brand.header'); ?></th>
            <th><?php echo $view['translator']->trans('milex.core.date.added'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($devices as $device): ?>
        <tr>
            <td>
                <i class="fa fa-fw fa-<?php echo ('smartphone' === $device['device']) ? 'mobile' : $device['device']; ?>"></i>
            </td>
            <td><?php echo $view['translator']->transConditional('milex.lead.device.'.$device['device'], ucfirst($device['device'])); ?></td>
            <td><?php echo $device['device_os_name']; ?></td>
            <td><?php echo $device['device_os_version']; ?></td>
            <td>
                <?php
                $clientInfo = Serializer::decode($device['client_info']);
                echo (is_array($clientInfo) && isset($clientInfo['name'])) ? $clientInfo['name'] : '';
                ?>
            </td>
            <td>
                <?php
                // Short codes are being removed from DeviceParser but there are values stored in the DB that may still depend on it
                $brandName = AbstractDeviceParser::getFullName($device['device_brand']);
                echo $brandName ?: $device['device_brand'];
                ?>
            </td>
            <td><?php echo $view['date']->toText($device['date_added'], 'utc'); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>