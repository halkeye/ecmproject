
<ul>
<?php
foreach ($registrations as $reg)
{
    $class_row = text::alternate('row_odd','row_even');
    echo '<li class="'.$class_row.'">';
    echo html::specialchars($reg);
    echo ' ';
    echo html::anchor('/convention/editReg/'.$reg->id,   html::image('img/edit-copy.png', 'Edit this account'));
    echo ' ';
    echo html::anchor('/convention/deleteReg/'.$reg->id, html::image('img/edit-delete.png', 'Delete this account'));
    echo '</li>';
}
?>
</ul>


<table>
    <tr>
        <td><?php
            echo form::open('/convention/checkoutPaypal', array('method'=>'get')); 
            echo form::submit('', Kohana::lang('convention.checkout_with_paypal')); 
            echo form::close();
        ?></td>
        <td><?php
            echo form::open('/convention/checkoutOther', array('method'=>'get')); 
            echo form::submit('', Kohana::lang('convention.checkout_with_other')); 
            echo form::close();
        ?></td>
    </tr>
</table>
