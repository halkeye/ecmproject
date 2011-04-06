<ul>
<?php
foreach ($passes as $pass)
{
    $class_row = text::alternate('row_odd','row_even');
    echo '<li class="'.$class_row.'">';
    echo html::chars($pass->name);
    echo '</li>';
}
?>
</ul>

