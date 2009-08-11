<form action="<?= $url ?>" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="<?= html::specialchars($business) ?>">
<input type="hidden" name="lc" value="CA">
<input type="hidden" name="item_name" value="<?= html::specialchars($itemName) ?>">
<input type="hidden" name="item_number" value="<?= intval($itemId) ?>">
<input type="hidden" name="amount" value="<?= sprintf('%01.2F', $price) ?>">
<input type="hidden" name="currency_code" value="CAD">
<input type="hidden" name="button_subtype" value="products">
<input type="hidden" name="no_shipping" value="2">
<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHosted">
<?php foreach ($fields as $key => $value): ?>
<input type="hidden" name="<?= html::specialchars($key) ?>" value="<?= html::specialchars($value) ?>">
<?php endforeach ?>
<input type="image" src="<?= url::base().$image_dir.'btn_buynow.gif' ?>" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="<?= url::base().$image_dir.'pixel.gif' ?>" width="1" height="1">
</form>
