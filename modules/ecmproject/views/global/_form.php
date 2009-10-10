<?php
echo "<table>";
foreach ($fields as $field => $fieldData)
{
    echo '<tr>';
    $classRow = text::alternate('odd','even');

    echo "<th class='fieldHeader fieldHeader$classRow'>";
    echo form::label($field, $fieldData['label']);
    if (isset($fieldData['required']) && $fieldData['required'])
        echo ' <span class="required">*</span>';
    echo '</th>';

    echo "<td class='fieldContent fieldContent$classRow'>";
    if (!isset($fieldData['type'])) { $fieldData['type'] = 'text'; }
    switch ($fieldData['type'])
    {
        case 'password':
            echo form::password($field, $form[$field]);
            break;
        case 'textarea':
            echo form::textarea(array('name'=>$field,'rows'=>4, 'cols'=>30), $form[$field]);
            break;
        case 'select':
            echo form::dropdown($field, $fieldData['values'], $form[$field]);
            break;
        case 'date':
            echo new View('global/_form_date', array('field'=>$field, 'fieldData'=>$fieldData, 'form'=>$form));
            break;
        default:
            echo form::input($field, $form[$field]);
            break;
    }
    echo '</td>';

    echo "<td class='fieldError fieldError$classRow'>";
    if (!empty ($errors[$field]))
        echo '<p class="errormsg">'.$errors[$field].'</p>';

    unset($errors[$field]); // any leftover errors need to be printed out somewhere
    echo '</td>';

    echo '</tr>';
}
echo '</table>';
