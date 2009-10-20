<?php
if (!isset($fieldData['type'])) { $fieldData['type'] = 'text'; }
$classRow = text::alternate('odd','even');

/* ex: View::set_global('field_lang_prefix', 'ecmproject.formtest_');
 * This is generating the label from the lang files 
 */
if (!$field_lang_prefix) { $field_lang_prefix = 'convention.registration_field_'; }
$label = Kohana::lang($field_lang_prefix . $field);
if (isset($fieldData['required']) && $fieldData['required'])
    $label .= ' <span class="required">*</span>';


switch ($fieldData['type'])
{
    case 'radio':
    case 'hidden':
        /* Don't show a label because we'll do something else */
        break;
    case 'date':
        echo form::label($field.'-year', $label);
        break;
    default:
        echo form::label($field, $label);
}
$attributes = '';
if ($hasError) { $attributes = 'class="fieldError"'; }

switch ($fieldData['type'])
{
    case 'radio':
        if ($fieldData['values'])
        {
            foreach ($fieldData['values'] as $radioValue => $label)
            {
                print form::label($field.'_'.$radioValue, $label);
                print form::radio($field.'_'.$radioValue, $radioValue, $value == $radioValue);
                print " ";
            }
        }
        break;
    case 'hidden':
        echo form::hidden($field, $value);
        break;
    case 'bool':
    case 'boolean':
    case 'checkbox':
        echo form::checkbox($field, $value, @$fieldData['selected'], $attributes);
        break;
    case 'password':
        echo form::password($field, $value, $attributes);
        break;
    case 'textarea':
        echo form::textarea(array('name'=>$field,'rows'=>4, 'cols'=>30), $value, $attributes);
        break;
    case 'select':
        $values = $fieldData['values'];
        $values[-1] = "";
        asort($values);
        echo form::dropdown($field, $values, $value, $attributes);
        break;
    case 'date':
        $months[-1] = '';
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
        $years = array_values(date::years(1900, date('Y', time())));
        $years = array_combine($years, $years);
        $years[-1] = "";
        ksort($years);
        $days = array_merge(array(-1=>" "), array_combine(range(1,31),range(1,31)));
        $days[-1] = "";
        ksort($years);

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
