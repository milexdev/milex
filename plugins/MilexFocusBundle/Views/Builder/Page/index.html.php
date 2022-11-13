<?php

$props = $focus['properties'];

echo $view->render(
    'MilexFocusBundle:Builder\Modal:index.html.php',
    [
        'focus'    => $focus,
        'preview'  => $preview,
        'clickUrl' => $clickUrl,
        'htmlMode' => $htmlMode,
    ]
);
