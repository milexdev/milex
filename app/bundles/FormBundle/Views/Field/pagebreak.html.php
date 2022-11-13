<?php

$containerType     = 'pagebreak-wrapper';
$defaultInputClass = 'pagebreak';

include __DIR__.'/field_helper.php';

$backButtonAttr = $nextButtonAttr = $inputAttr;
$classPrefix    = 'btn btn-default milex-pagebreak-';

$appendAttribute($backButtonAttr, 'class', $classPrefix.'back');
$appendAttribute($nextButtonAttr, 'class', $classPrefix.'next'.((!empty($inForm)) ? ' mr-lg ' : ''));

if (empty($inForm)) {
    $containerAttr .= ' data-milex-form-pagebreak="'.$fieldPage.'"';

    // Hidden by default and only visible if JS makes it so
    $appendAttribute($containerAttr, 'style', 'display: none;');
}

if (empty(trim($field['properties']['prev_page_label']))) {
    $appendAttribute($backButtonAttr, 'style', 'display: none;');
}

$html = <<<HTML

            <div $containerAttr>
                <button type="button" $backButtonAttr data-milex-form-pagebreak-button="prev">{$field['properties']['prev_page_label']}</button>
                <button type="button" $nextButtonAttr data-milex-form-pagebreak-button="next">{$field['properties']['next_page_label']}</button>
            </div>

HTML;

echo $html;
