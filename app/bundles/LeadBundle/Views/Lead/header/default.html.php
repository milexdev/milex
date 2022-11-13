<?php

echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
    'sessionVar' => 'lead',
    'orderBy'    => 'l.'.$column,
    'text'       => $label,
    'class'      => 'col-lead-'.$column.' '.$class,
]);
