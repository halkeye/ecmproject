<div id='form'>
<br />
<!-- CONTENT: Expect initial convention id for this pass to be associated to. -->
<?php
	View::set_global('field_lang_prefix', 'admin.payment_field_');

	/* Set default values for unset variables. */
	if (!isset($row['name']))
		$row['name'] = '';

	if (!isset($row['location']))
		$row['location'] = '';

	echo form::open("admin/$callback");
?>
	<h1>Payment Information</h1>
	<p>For manual payment entries you are required to enter in the amount paid as well as the payment status and it's type. Payment date is automatically
	set to the date and time of when you successfully submit payment.</p>
	<fieldset>

		<?php
			foreach (array('type', 'txn_id', 'receipt_id', 'mc_gross', 'payment_status') as $field)
			{
				echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
			}
		?>
	</fieldset>
	<fieldset>
		<button type="submit">Continue</button>
	</fieldset>
<?php echo form::close(); ?>
</div>
