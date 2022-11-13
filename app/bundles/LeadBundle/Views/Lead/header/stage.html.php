<?php

echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
    'sessionVar' => 'lead',
    'orderBy'    => 'l.stage_id',
    'text'       => 'milex.lead.stage.label',
    'class'      => 'col-lead-stage '.$class,
]);
