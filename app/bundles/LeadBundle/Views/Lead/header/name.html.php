<?php

echo $view->render('MilexCoreBundle:Helper:tableheader.html.php', [
    'sessionVar' => 'lead',
    'orderBy'    => 'l.lastname, l.firstname, l.company, l.email',
    'text'       => 'milex.core.name',
    'class'      => 'col-lead-name '.$class,
]);
