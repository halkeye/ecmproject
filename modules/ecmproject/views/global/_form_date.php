<?php
### Generate list of years
foreach (date::months() as $month)
{
    $months[$month] = Kohana::lang('calendar.' . strtolower(date('F', mktime(0,0,0,$month))));
}

$year = '';
$month = '';
$day = '';

if ($form[$field])
{
    $date = strtotime($form[$field]);
    $year = date('Y', $date);
    $month = date('n', $date);
    $day = date('d', $date);
}
$years = array_values(array_reverse(date::years(1900, date('Y', time()))));
echo form::dropdown($field.'-year', array_combine($years, $years), $year );
echo ' ';
echo form::dropdown($field.'-month', $months, $month );
echo ' ';
echo form::dropdown($field.'-day', array_combine(range(1,31),range(1,31)) , $day );
