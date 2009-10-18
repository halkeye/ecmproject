<?php
$classRow = text::alternate('odd','even');

$label = $fieldData['label'];
if (isset($fieldData['required']) && $fieldData['required'])
    $label .= ' <span class="required">*</span>';

echo form::label($field, $label);
$attributes = '';
if ($hasError) { $attributes = 'class="fieldError"'; }

if (!isset($fieldData['type'])) { $fieldData['type'] = 'text'; }
switch ($fieldData['type'])
{
    case 'password':
        echo form::password($field, $value, $attributes);
        break;
    case 'textarea':
        echo form::textarea(array('name'=>$field,'rows'=>4, 'cols'=>30), $value, $attributes);
        break;
    case 'select':
        $values = $fieldData['values'];
        array_unshift($values, "");
        echo form::dropdown($field, $values, $value, $attributes);
        break;
    case 'date':
        ### Generate list of years
        foreach (date::months() as $month)
        {
            $months[$month] = Kohana::lang('calendar.' . strtolower(date('F', mktime(0,0,0,$month))));
        }

        $year = '';
        $month = '';
        $day = '';

        if ($value && $value != '0000-00-00')
        {
            $date = strtotime($value);
            $year = date('Y', $date);
            $month = date('n', $date);
            $day = date('d', $date);
        }
        $years = array_values(array_reverse(date::years(1900, date('Y', time()))));
        $years = array_combine($years, $years);
        $days = array_combine(range(1,31),range(1,31));

        array_unshift($years, "");
        array_unshift($months, "");
        array_unshift($days, "");
        echo form::dropdown($field.'-year', $years, $year, $attributes );
        echo ' ';
        echo form::dropdown($field.'-month', $months, $month, $attributes );
        echo ' ';
        echo form::dropdown($field.'-day', $days, $day, $attributes );
        echo '<br />';
        break;
    default:
        echo form::input($field, $value, $attributes);
        break;
}
