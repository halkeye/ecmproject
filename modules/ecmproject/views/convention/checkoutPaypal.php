
<?php echo form::open($paypal_url); ?>
<!-- see https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_Appx_websitestandard_htmlvariables -->
<input type="hidden" name="cmd" value="_cart"/>
<input type="hidden" name="upload" value="1"/>
<!-- <input type="hidden" name="cpp_header_image" value="/path/to/image"> -->
<!-- <input type="hidden" name="no_shipping" value="2"> FIXME: Do we want to mark the order as non shippable? -->
<!-- shopping_url -     
The URL of the page on the merchant website that buyers return to when they click the Continue Shopping button on the PayPal Shopping Cart page.
-->
<input type="hidden" name="currency_code" value="CAD" />
<?php foreach (array('business', 'notify_url', 'cancel_url') as $key) {
    if (!isset($$key)) { continue; }
    echo form::hidden($key, $$key); 
}
echo form::hidden('return', $return_url); 
echo form::hidden('cbt', "Return to Anime Evolution Registration"); 
?>

<ul>
<?php
$regids = array();
foreach ($registrations as $reg)
{
    $id = count($regids)+1;
    $regids[] = $reg->id;

    $class_row = text::alternate('row_odd','row_even');
    echo '<li class="'.$class_row.'">';
    $name = $reg->pass->name . ' - ' . $reg->badge;
    echo html::specialchars($name);
    echo form::hidden("item_number_$id", $reg->id . '|'. $reg->pass->id);
    echo form::hidden("item_name_$id", $name);
    echo form::hidden("amount_$id", sprintf('%01.2F', $reg->pass->price));
    echo form::hidden("quantity_$id", 1);
    echo '</li>';
}
echo form::hidden("custom", implode('|', $regids));
?>
</ul>

<br />
<?php
echo form::submit('', Kohana::lang('convention.confirm_checkout_with_paypal')); 
echo form::close(); 
 
?>

