<div id="list">
    <p><?php echo __('convention.checkout_header'); ?></p>
    <br />
    <p>Thank you for registering for Anime Evolution 2010! To qualify for the
    pre-reg price that you have registered for, you must submit the payment
    within one week from the deadline date. Mail in payments must be postmarked no later
    then the deadline date. Failing this, you will be required to
    pay the at-door price for your badge(s).</p>
    <br />
    <table width='100%'>    
        <tr>    
            <th width='40%'>For</th>
            <th width='30%'>Item</th>
            <th width='10%'>Price</th>  
            <th width='5%'>Edit</th>
            <th width='5%'>Delete</th>
            <th width='10%'>Status</th>  
        </tr><?php
$noBadRegistrations = 0;
if (count($registrations) > 0)
{
    $noBadRegistrations = 1;
    foreach ($registrations as $reg)
    {
        $class_row = text::alternate('odd','even');
        $expiredReg = 0;
        if  (time() >= $reg->pass->endDate)
        {
            $expiredReg = 1;
            $noBadRegistrations = 0;
        }
        echo '<tr class="'.$class_row.'">';
        echo '<td>' . HTML::chars($reg->gname . ' ' . $reg->sname) . '</td>';
        if (!$expiredReg)
        {
            echo '<td>' . HTML::chars($reg->pass->name) . '</td>';
            echo '<td>' . HTML::chars(sprintf('$%01.2F', $reg->pass->price)) . '</td>';
        }
        else
        {
            echo '<td class="expiredReg" colspan="2">The registration pass you\'ve selected has expired. Please click edit to choose a different pass.</td>';
        }
        echo '<td>'.HTML::anchor('/convention/editReg/'.$reg->id,   HTML::image(url::site('/static/img/edit-copy.png',TRUE), array('tite'=>'Edit this account'))) . '</td>';
        echo '<td>'.HTML::anchor('/convention/deleteReg/'.$reg->id, HTML::image(url::site('/static/img/edit-delete.png',TRUE), array('tite'=>'Delete this account'))) . '</td>';
        echo '<td>'.$reg->statusToString().'</td>';
        echo '</tr>';
    }
}
else
{
    echo '<td colspan="6">No Registrations yet</td>';
}
?>
    </table>
    <table>
        <?php if ($noBadRegistrations): ?>
        <tr>
            <td><?php
                echo form::open($paypal_url, array('target'=>'_top'));
                echo form::hidden('cmd', '_cart');
                echo form::hidden('upload', '1');
                echo form::hidden('no_shipping', '2');
                ### FIXME - make currency code
                echo form::hidden('currency_code', 'CAD');
                foreach (array('business', 'notify_url', 'cancel_url') as $key) {
                    if (!isset($$key)) { continue; }
                    echo form::hidden($key, $$key); 
                }
                echo form::hidden('return', $return_url); 
                echo form::hidden('cbt', "Return to Anime Evolution Registration"); 
                $regids = array();
                foreach ($registrations as $reg)
                {
                    if ($reg->status != Model_Registration::STATUS_UNPROCESSED) { continue; }
                    $id = count($regids)+1;
                    $regids[] = $reg->id;

                    $name = $reg->pass->name; # . ' - ' . $reg->badge;
                    echo form::hidden("item_number_$id", $reg->id . '|'. $reg->pass->id);
                    echo form::hidden("item_name_$id", $name);
                    echo form::hidden("amount_$id", sprintf('%01.2F', $reg->pass->price));
                    echo form::hidden("quantity_$id", 1);
                }
                echo form::hidden("custom", implode('|', $regids));
                echo form::submit('', __('convention.checkout_with_paypal')); 
                echo form::close();
            ?></td>
            <td><?php
                echo form::open('/convention/checkoutOther', array('method'=>'get')); 
                echo form::submit('', __('convention.checkout_with_other')); 
                echo form::close();
            ?></td>
        </tr>
        <?php endif ?>
        <tr>
            <td><?php
                echo form::open(url::site('/convention/addRegistration', TRUE), array('method'=>'post')); 
                $options = array();
                foreach ($passes as $pass)
                {
                    $options[$pass->convention->name][$pass->id] = "$pass";
                }
                echo form::select('pass_id', $options);
                echo form::submit('', __('Add new registration to checkout')); 
                echo form::close();
            ?></td>
        </tr>
    </table>
</div>
