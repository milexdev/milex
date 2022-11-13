<?php

$appendAttribute = function (&$attributes, $attributeName, $append) {
    if (false === stripos($attributes, "{$attributeName}=")) {
        $attributes .= ' '.$attributeName.'="'.$append.'"';
    } else {
        $attributes = str_ireplace($attributeName.'="', $attributeName.'="'.$append.' ', $attributes);
    }
};

if (!isset($defaultInputFormClass)) {
    $defaultInputFormClass = '';
}

if (!isset($defaultLabelClass)) {
    $defaultLabelClass = 'label';
}

if (!isset($formName)) {
    $formName = '';
}

$properties = $field['properties'];

$defaultInputClass = 'milexform-'.$defaultInputClass;
$defaultLabelClass = 'milexform-'.$defaultLabelClass;

$name = '';
if (empty($ignoreName)) {
    $inputName = 'milexform['.$field['alias'].']';
    if (!empty($properties['multiple'])) {
        $inputName .= '[]';
    }
    $name = ' name="'.$inputName.'"';
}

if (in_array($field['type'], ['checkboxgrp', 'radiogrp', 'textarea'])) {
    $value = '';
} else {
    $value = (isset($field['defaultValue'])) ? ' value="'.$field['defaultValue'].'"' : ' value=""';
}

if (empty($ignoreId)) {
    $inputId = 'id="milexform_input'.$formName.'_'.$field['alias'].'"';
    $labelId = 'id="milexform_label'.$formName.'_'.$field['alias'].'" for="milexform_input'.$formName.'_'.$field['alias'].'"';
} else {
    $inputId = $labelId = '';
}

$inputAttr = $inputId.$name.$value;
$labelAttr = $labelId;

if (!empty($properties['placeholder'])) {
    $inputAttr .= ' placeholder="'.$properties['placeholder'].'"';
}

// Label and input
if (!empty($inForm)) {
    if (in_array($field['type'], ['button', 'pagebreak'])) {
        $defaultInputFormClass .= ' btn btn-default';
    }
    $labelAttr .= ' class="'.$defaultLabelClass.'"';
    $inputAttr .= ' disabled="disabled" class="'.$defaultInputClass.$defaultInputFormClass.'"';
} else {
    if ($field['labelAttributes']) {
        $labelAttr .= ' '.htmlspecialchars_decode($field['labelAttributes']);
    }

    $appendAttribute($labelAttr, 'class', $defaultLabelClass);

    if ($field['inputAttributes']) {
        $inputAttr .= ' '.htmlspecialchars_decode($field['inputAttributes']);
    }

    $appendAttribute($inputAttr, 'class', $defaultInputClass);
}

// Container
$containerAttr = 'id="milexform'.$formName.'_'.$id.'" '.htmlspecialchars_decode($field['containerAttributes']);

if (!isset($containerClass)) {
    $containerClass = $containerType;
}
$order                 = (isset($field['order'])) ? $field['order'] : 0;
$defaultContainerClass = 'milexform-row milexform-'.$containerClass.' milexform-field-'.$order;

if ($field['parent'] && isset($fields[$field['parent']])) {
    $values = implode('|', $field['conditions']['values']);

    if (!empty($field['conditions']['any']) && 'notIn' != $field['conditions']['expr']) {
        $values = '*';
    }

    $containerAttr .= " data-milex-form-show-on=\"{$fields[$field['parent']]->getAlias()}:".$values.'" data-milex-form-expr="'.$field['conditions']['expr'].'"';

    $defaultContainerClass .= '  milexform-field-hidden';
}

// Field is required
$validationMessage = '';
if (isset($field['isRequired']) && $field['isRequired']) {
    $required = true;
    $defaultContainerClass .= ' milexform-required';
    $validationMessage = $field['validationMessage'];
    if (empty($validationMessage)) {
        $validationMessage = $view['translator']->trans('milex.form.field.generic.required', [], 'validators');
    }

    $containerAttr .= " data-validate=\"{$field['alias']}\" data-validation-type=\"{$field['type']}\"";

    if (!empty($properties['multiple'])) {
        $containerAttr .= ' data-validate-multiple="true"';
    }
} elseif (!empty($required)) {
    // Forced to be required
    $defaultContainerClass .= ' milexform-required';
}

$appendAttribute($containerAttr, 'class', $defaultContainerClass);

// Setup list parsing
if (isset($list) || isset($properties['syncList']) || isset($properties['list']) || isset($properties['optionlist'])) {
    $parseList     = [];
    $isBooleanList = false;

    if (!isset($contactFields)) {
        $contactFields = [];
    }
    if (!isset($companyFields)) {
        $companyFields = [];
    }
    $formFields = array_merge($contactFields, $companyFields);
    if (!empty($properties['syncList']) && !empty($field['leadField']) && isset($formFields[$field['leadField']])) {
        $leadFieldType = $formFields[$field['leadField']]['type'];
        switch (true) {
            case !empty($formFields[$field['leadField']]['properties']['list']):
                $parseList = $formFields[$field['leadField']]['properties']['list'];
                break;
            case 'boolean' == $leadFieldType:
                $parseList     = [
                    0 => $formFields[$field['leadField']]['properties']['no'],
                    1 => $formFields[$field['leadField']]['properties']['yes'],
                ];
                $isBooleanList = true;
                break;
            case 'country' == $leadFieldType:
                $list = \Milex\LeadBundle\Helper\FormFieldHelper::getCountryChoices();
                break;
            case 'region' == $leadFieldType:
                $list = \Milex\LeadBundle\Helper\FormFieldHelper::getRegionChoices();
                break;
            case 'timezone' == $leadFieldType:
                $list = \Milex\LeadBundle\Helper\FormFieldHelper::getTimezonesChoices();
                break;
            case 'locale':
                $list = \Milex\LeadBundle\Helper\FormFieldHelper::getLocaleChoices();
                break;
        }
    }

    if (empty($parseList)) {
        if (isset($list)) {
            $parseList = $list;
        } elseif (!empty($properties['optionlist'])) {
            $parseList = $properties['optionlist'];
        } elseif (!empty($properties['list'])) {
            $parseList = $properties['list'];
        }

        if (isset($parseList['list'])) {
            $parseList = $parseList['list'];
        }
    }

    if ($field['leadField'] && !empty($formFields[$field['leadField']]['type'])
        && in_array(
            $formFields[$field['leadField']]['type'],
            ['datetime', 'date']
        )) {
        $tempLeadFieldType = $formFields[$field['leadField']]['type'];
        foreach ($parseList as $key => $aTemp) {
            if ($date = ('datetime' == $tempLeadFieldType ? $view['date']->toFull($aTemp['label']) : $view['date']->toDate($aTemp['label']))) {
                $parseList[$key]['label'] = $date;
            }
        }
    }

    $list = $isBooleanList
        ?
        \Milex\FormBundle\Helper\FormFieldHelper::parseBooleanList($parseList)
        :
        \Milex\FormBundle\Helper\FormFieldHelper::parseList($parseList);

    $firstListValue = reset($list);
}
