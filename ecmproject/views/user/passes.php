<table>
<?php
$colsPerRow = 4;
$count = 0;
foreach ($passes as $pass) 
{
    if (($count++ % $colsPerRow) == 0) echo '<tr>';

    echo '<td>';
    echo $pass->paypalButton($notify_url, $return_url, $cancel_url); 
    echo '<br />';
    echo '<b>'. $pass->name . '</b>';
    echo '</td>';

    if (($count % $colsPerRow) == 0) echo '</tr>';
}
if (($count % $colsPerRow) != 0) echo '</tr>';
?>
</table>

