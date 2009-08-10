<br />
<h5><?= $itemName ?></h5>
<form action="<?= isset($url) ? $url : Kohana::config('paypal.url') ?>" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="<?= isset($business) ? $business : Kohana::config('paypal.business') ?>">
<input type="hidden" name="lc" value="CA">
<input type="hidden" name="item_name" value="<?= $itemName ?>">
<input type="hidden" name="item_number" value="<?= $itemId ?>">
<input type="hidden" name="amount" value="<?= $price ?>.00">
<input type="hidden" name="currency_code" value="CAD">
<input type="hidden" name="button_subtype" value="products">
<input type="hidden" name="no_shipping" value="2">
<input type="hidden" name="bn" value="PP-BuyNowBF:btn_buynowCC_LG.gif:NonHosted">
<input type="image" src="<?= url::base().Kohana::config('paypal.image_dir').'/btn_buynowCC_LG.gif' ?>" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="<?= url::base().Kohana::config('paypal.image_dir').'/pixel.gif' ?>" width="1" height="1">
</form>

<br/>
