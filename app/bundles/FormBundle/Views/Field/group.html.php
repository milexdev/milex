<?php

use Milex\CoreBundle\Helper\InputHelper;

$containerType     = "{$type}grp";
$defaultInputClass = "{$containerType}-{$type}";
$ignoreId          = true;
$ignoreName        = ('checkbox' == $type);

include __DIR__.'/field_helper.php';

$optionLabelAttr = (isset($properties['labelAttributes'])) ? $properties['labelAttributes'] : '';
$wrapDiv         = true;

$defaultOptionLabelClass = 'milexform-'.$containerType.'-label';
if (false === stripos($optionLabelAttr, 'class')) {
    $optionLabelAttr .= ' class="'.$defaultOptionLabelClass.'"';
} else {
    $optionLabelAttr = str_ireplace('class="', 'class="'.$defaultOptionLabelClass.' ', $optionLabelAttr);
    $wrapDiv         = false;
}

$count   = 0;
$firstId = 'milexform_'.$containerType.'_'.$type.'_'.$field['alias'].'_'.InputHelper::alphanum(InputHelper::transliterate($firstListValue)).'1';

$label = (!$field['showLabel']) ? '' : <<<HTML

                <label $labelAttr for="$firstId">{$field['label']}</label>
HTML;

$help = (empty($field['helpMessage'])) ? '' : <<<HTML

                <span class="milexform-helpmessage">{$field['helpMessage']}</span>
HTML;

$options = [];
$counter = 0;
foreach ($list as $listValue => $listLabel):

$id               = $field['alias'].'_'.InputHelper::alphanum(InputHelper::transliterate($listValue)).$counter;
$checked          = ($field['defaultValue'] === $listValue) ? 'checked="checked"' : '';
$checkboxBrackets = ('checkbox' == $type) ? '[]' : '';

$option = <<<HTML
                    <input {$inputAttr}{$checked} name="milexform[{$field['alias']}]{$checkboxBrackets}" id="milexform_{$containerType}_{$type}_{$id}" type="{$type}" value="{$view->escape($listValue)}" />
                    <label id="milexform_{$containerType}_label_{$id}" for="milexform_{$containerType}_{$type}_{$id}" {$optionLabelAttr}>$listLabel</label>
HTML;

if ($wrapDiv):
$option = <<<HTML

                <div class="milexform-{$containerType}-row">$option
                </div>
HTML;
endif;

$options[] = $option;
++$counter;
endforeach;

$optionHtml = implode('', $options);

$html = <<<HTML

            <div $containerAttr>{$label}{$help}{$optionHtml}
                <span class="milexform-errormsg" style="display: none;">$validationMessage</span>
            </div>

HTML;

echo $html;
