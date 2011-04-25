<p>Select a ticket that you wish to purchase from the dropdown and click the <strong>[Add to Cart]</strong> button. You may add more than one ticket by repeating this process.</p>
<?php echo form::open(url::site('/convention/addRegistration', TRUE), array('method'=>'post')); ?>
<fieldset>
	<?php
		$options[-1] = "SELECT A TICKET";
		foreach ($passes as $pass) {						
			$options[$pass->convention->name][$pass->id] = "$pass";
		}
		echo form::select('pass_id', $options);
	?>
	<button type="submit"><?php echo __('Add to Cart'); ?></button>
</fieldset>
<?php echo form::close(); ?>

<h4 class='theader'>Shopping Cart</h4>
<table width='100%'>    
	<tr>    
		<th width='40%'>For</th>
		<th width='40%'>Item</th>
		<th width='10%'>Price</th>  
		<th width='10%'>Status</th> 
		<th width='5%'>Delete</th>
	</tr>
	
	<?php
		$noBadRegistrations = 0;
		$total_cost = 0;
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
					
					//Don't have it go belly up if it's not a number.
					if (is_numeric($reg->pass->price)) {
						$total_cost += $reg->pass->price;
					}
				}
				else
				{
					echo '<td class="expiredReg" colspan="2">The registration pass you\'ve selected has expired. Please click edit to choose a different pass.</td>';
				}
				
				echo '<td>'.$reg->statusToString().'</td>';
				echo '<td class="center">'.HTML::anchor('/convention/deleteReg/'.$reg->id, HTML::image(url::site('/static/img/edit-delete.png',TRUE), array('tite'=>'Delete this account')), null, null, true) . '</td>';
				echo '</tr>';
			}
			
			echo '<tr><td colspan=2 class="total_header">Total: </td><td colspan=3 class="total">' . sprintf('$%01.2F', $total_cost) . '</td></tr>';
		}
		else
		{
			echo '<td colspan="5">No Registrations yet</td>';
		}
?>	
</table>

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
		echo form::hidden('cbt', "Finalize Registration"); 
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
		# from http://corpocrat.com/2010/12/31/paypal-return-url-issue-with-missing-get-parameters/
		echo form::hidden('rm', '2');

		echo form::hidden("custom", implode('|', $regids));
		//echo form::submit('', __('Add new registration to checkout'));
		echo "<p class='inline'>Added everything you want? Click the <strong>Checkout with Paypal</strong> button to checkout your tickets.</p>";
		echo '<button type="submit" class="right">' . __("Checkout with Paypal") . '</button>';
		echo form::close();
	?></td>
</tr>
<?php endif ?>

