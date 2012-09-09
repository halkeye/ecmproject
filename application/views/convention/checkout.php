<p class='warning'><?php echo html::image(url::site('/static/img/warning.png', TRUE), array('alt' => 'WARNING')); ?><strong>Please ensure that the name
you provide (shown above) is the same as written on your government ID. </strong> We will match the name provided here to the government ID you bring when picking up your ticket.</p>
<br />
<p>
Select a ticket that you wish to purchase from the dropdown and click the <strong>[Add to Cart]</strong> button. You may add more than one ticket by repeating this process. </p>

<div id="dialog" title="Specify your Date of Birth" style="display:none">
	<br />
	<p>This ticket requires your Date of Birth (DOB) to determine whether you are the age of majority.</p>
	<p class="validateTips"></p>
	<p>Date of Birth (YYYY-MM-DD): <input type="text" id="dob_dialog"></p>
</div>
<script>
	dob = $( "#dob_dialog" );
	tips = $( ".validateTips" );

	function updateTips( t ) {
		tips
			.text( t )
			.addClass( "ui-state-highlight" );
		setTimeout(function() {
			tips.removeClass( "ui-state-highlight", 1500 );
		}, 500 );
	}

	function checkRegexp( o, regexp, n ) {
		if ( !( regexp.test( o.val() ) ) ) {
			o.addClass( "ui-state-error" );
			updateTips( n );
			return false;
		} else {
			return true;
		}
	}

	function doDOB() {
		var pass_id = $( "#select_pass" ).val();
		if (! $("#dob_" + pass_id).val() )
		{
			$("#form_add_ticket").submit();
			return;
		}

		$( "#dialog" ).dialog({
			modal: true,
			width: 500,
			buttons: {
				"Add to Cart" : function() {
					var valid = true;
					valid = checkRegexp( dob, /^(\d{4})-(\d{1,2})-(\d{1,2})/, "Date of birth cannot be empty and must be specified YYYY-MM-DD." );

					if (valid) {
						$("#dob").val( dob.val() );
						$("#form_add_ticket").submit();
					}
				},
				"Cancel" : function() {
					$( this ).dialog( "close" );
				}
			}
		});
	}
</script>



<?php echo form::open(url::site('/convention/addRegistration', TRUE), array('id' => 'form_add_ticket', 'method'=>'post')) . "\n"; ?>
<fieldset>
	<?php
	$options[-1] = "SELECT A TICKET";
	foreach ($passes as $pass) {
		$options[$pass->convention->name][$pass->id] = "$pass";

		if ($pass->requireDOB) {
			echo form::hidden("dob_" . $pass->id, true, array('id' => "dob_" . $pass->id));
		}
	}
	echo form::select('pass_id', $options, NULL, array('id' => "select_pass")) . "\n";
	echo form::hidden("dob", "", array('id' => 'dob'));
	echo form::input('add_ticket', __('Add to Cart'), array('type' => 'button', 'onclick' => 'doDOB()', 'class' => 'submit'));
	//echo form::input('add_ticket', __('Add to Cart'), array('type' => 'button', 'id' => 'add_ticket', 'class' => 'submit')) . "\n";
	?>
</fieldset>
<?php echo form::close(); ?>

<table width='100%' class='border'>
<tr><th class='header' colspan=5>Shopping Cart</th></tr>
<tr>
	<th width='40%'>For</th>
	<th width='40%'>Item</th>
	<th width='10%'>Price</th>
	<th width='10%'>Status</th>
	<th width='5%'>Delete</th>
</tr>

<?php
	$total_cost = 0;
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
				echo '<td>' . HTML::chars($reg->convention->name . ' - ' . $reg->pass->name) . '</td>';
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
		echo form::submit('paypal', __('Checkout with Paypal'), array('class' => 'fsubmit'));
		echo "<p>Added everything you want? Click the <strong>Checkout with Paypal</strong> button to checkout your tickets.</p>";
		echo form::close();
	?></td>
</tr>
<?php endif ?>

<p><strong>Need to find your registration IDs?</strong> You can <?php echo html::anchor('/user/', __('view current/past registrations (including Registration IDs) on the My Accounts page'), null, null, true); ?>.</p>


