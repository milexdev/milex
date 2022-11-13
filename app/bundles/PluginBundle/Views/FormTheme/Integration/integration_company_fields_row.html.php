<?php

$containerId    = 'companyFieldsContainer';
$numberOfFields = ($form->offsetExists('update_milex_company1')) ? 5 : 4;
$object         = 'company';

include __DIR__.'/fields_row.html.php';
