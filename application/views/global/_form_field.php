<div>
<?php
if (!isset($fieldData['type'])) { $fieldData['type'] = 'text'; }
$classRow = text::alternate('odd','even');

/* Set main label: empty() checks both variable set and empty status (false) */
$label = empty($fieldData['label']) ? $field : $fieldData['label'];	
$sub_label = empty($fieldData['sub_label']) ? '' : $fieldData['sub_label'];
$label_attributes = array();

/* Add required mark */
if (isset($fieldData['required']) && $fieldData['required']) {
	$label .= ' <span class="required">*</span>';	
}

/* Add sub-label or lack thereof */
if ($sub_label) {
	$label .= ' <span class="small">' . $sub_label . '</span>';	
}
else {
	$label_attributes = array('class' => 'nosub');
}

/* Generate label */
switch ($fieldData['type'])
{
    case 'radio':
    case 'hidden':
        /* Don't show a label because we'll do something else */
        break;
    case 'date':
        echo form::label($field.'-year', $label, $label_attributes);
        break;
    default:
        echo form::label($field, $label, $label_attributes);
}

$attributes = null;
if ($hasError) { 
	//$attributes = array();
	$attributes['class'] = "inline"; 
} 

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
        #selected should be value, value should be if $fieldData['value]'
		$attributes['class'] = 'checkbox'; //Hack (for the time being).
        echo form::checkbox($field, 1, (bool)$value, $attributes);
        break;
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
        $values = @$fieldData['values'];
        if (!$values) $values = array();
        $values[-1] = "";
        asort($values);
        $attributes['class'] .= ' block';
        echo form::select($field, $values, $value, $attributes);
        break;
    case 'date':
        $months[-1] = '';
        ### Generate list of years
        foreach (date::months() as $month)
        {
            $months[$month] = __('calendar.' . strtolower(date('F', mktime(0,0,0,$month, 1))));
        }

        $year = '';
        $month = '';
        $day = '';
		
		/* Deal with cases where we feed in a UNIX timestamp value instead - Steve does this a lot (feed DB rows directly in as_array) */
		if ($value && is_numeric($value))
		{	
			$year = date('Y', $value);
            $month = date('n', $value);
            $day = date('d', $value);		
		}		
        else if ($value && $value != '0000-00-00')
        {
            $date = strtotime($value);
            $year = date('Y', $date);
            $month = date('n', $date);
            $day = date('d', $date);
        }
        $years = array_values(date::years(1900, date('Y', time() + (31536000 * 5)))); //Allow for up to 5 years ahead.
        $years = array_combine($years, $years);
        $years[-1] = "";
        ksort($years);
        $days = array_merge(array(-1=>" "), array_combine(range(1,31),range(1,31)));
        $days[-1] = "";
        ksort($years);

        echo form::select($field.'-year', $years, $year, $attributes );
        echo ' ';
        echo form::select($field.'-month', $months, $month, $attributes );
        echo ' ';
        echo form::select($field.'-day', $days, $day, $attributes );
        echo '<br />';
        break;
    default:
        echo form::input($field, $value, $attributes);
        break;
}
?>
</div>
