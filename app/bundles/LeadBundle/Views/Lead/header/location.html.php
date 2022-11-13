<?php

echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
    'sessionVar' => 'lead',
    'orderBy'    => 'l.city, l.state',
    'text'       => 'milex.lead.lead.thead.location',
    'class'      => 'col-lead-location '.$class,
]);
