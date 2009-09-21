
<?php echo form::open($url); ?>
<input type="hidden" name="cmd" value="_cart"/>
<input type="hidden" name="upload" value="1"/>
<input type="hidden" name="currency_code" value="CAD">
<?php foreach (array('business', 'notify_url', 'return_url', 'cancel_url') as $key) {
    echo form::hidden($key, $$key); 
} ?>

<ul>
<?php
$id = 1;
foreach ($registrations as $reg)
{
    echo '<li>';
    $name = $reg->pass->name . ' - ' . $reg->badge;
    echo html::specialchars($name);
    echo form::hidden("item_number_$id", $reg->pass->id);
    echo form::hidden("item_name_$id", $name);
    echo form::hidden("amount_$id", sprintf('%01.2F', $reg->pass->price));
    echo form::hidden("quantity_$id", 1);
    echo '</li>';
    $id++;
}
?>
</ul>

<?php if (!$verifiedAccount) { echo ' FIXME - Unable to checkout until verified '; } ?>
<input type="submit" value="Checkout Paypal" <?php if (!$verifiedAccount) { echo ' disabled="disabled" '; } ?> />
<?php echo form::close(); ?>

