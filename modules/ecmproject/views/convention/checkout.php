
<ul>
<?php
foreach ($registrations as $reg)
{
    $class_row = text::alternate('row_odd','row_even');
    echo '<li class="'.$class_row.'">';
    $name = $reg->pass->name . ' - ' . $reg->badge;
    echo html::specialchars($name);
    echo '</li>';
}
?>
</ul>


<table>
    <tr>
        <td><?php
            echo form::open('/convention/checkoutPaypal', array('method'=>'get')); 
            echo form::submit('', Kohana::lang('convention.checkout_with_paypal')); 
        ?></td>
        <td><?php
            echo form::open('/convention/checkoutOther', array('method'=>'get')); 
            echo form::submit('', Kohana::lang('convention.checkout_with_other')); 
        ?></td>
    </tr>
</table>
