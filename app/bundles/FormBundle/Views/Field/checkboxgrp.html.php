<?php

if (isset($field['defaultValue']) && '' !== $field['defaultValue']) {
    $hiddenDefault = $view->render(
        'MilexFormBundle:Field:hidden.html.php',
        [
            'field'         => $field,
            'fields'        => isset($fields) ? $fields : [],
            'inForm'        => (isset($inForm)) ? $inForm : false,
            'id'            => $id,
            'formId'        => (isset($formId)) ? $formId : 0,
            'type'          => 'checkbox',
            'formName'      => (isset($formName)) ? $formName : '',
            'contactFields' => (isset($contactFields)) ? $contactFields : [],
            'companyFields' => (isset($companyFields)) ? $companyFields : [],
        ]
    );

    echo str_replace('<input', '<input value="'.$field['defaultValue'].'"', $hiddenDefault);
}

echo $view->render(
    'MilexFormBundle:Field:group.html.php',
    [
        'field'         => $field,
        'inForm'        => (isset($inForm)) ? $inForm : false,
        'id'            => $id,
        'formId'        => (isset($formId)) ? $formId : 0,
        'type'          => 'checkbox',
        'formName'      => (isset($formName)) ? $formName : '',
        'contactFields' => (isset($contactFields)) ? $contactFields : [],
        'companyFields' => (isset($companyFields)) ? $companyFields : [],
        'fields'        => isset($fields) ? $fields : null,
    ]
);
