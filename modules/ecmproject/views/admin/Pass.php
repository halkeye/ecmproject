<div id='form'>
<br />
<!-- CONTENT: Expect initial convention id for this pass to be associated to. -->
<?php 	
	View::set_global('field_lang_prefix', 'admin.pass_field_');
	
	/* Set default values for unset variables. */
	if (!isset($row['name']))
		$row['name'] = '';
		
	if (!isset($row['price']))
		$row['price'] = '';
		
	if (!isset($crows))
		$crows = array();
	
	if (!isset($convention_id))
		$convention_id = '';

	if (!isset($row['minAge']))
		$row['minAge'] = 0;
	
	if (!isset($row['maxAge']))
		$row['maxAge'] = 255;
		
	if (!isset($row['isPurchasable']))
		$row['isPurchasable'] = 0;

	echo form::open("admin/$callback"); 
?>
	<h1>General Information</h1>
	<p>At a minimum, a badge requires a name and a price. Upon creation, this badge will be available for purchase through
	normal registration unless otherwise specified. </p>		
	<fieldset>		
		<?php
			foreach (array('name', 'price', 'convention_id') as $field)
			{
				echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
			}		
		?>
	</fieldset>
	<h1>Availability</h1>
	<p>A badge can be set to be available for a period of time. For instance, a badge can be set so that it is only purchasable from October 23rd, 2009 to
	December 23rd, 2009. </p>		
	<fieldset>	
		<?php 	
		foreach (array('startDate', 'endDate') as $field)
		{
			echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
			echo '<br />';
		}
		?>
	</fieldset>
	<h1>Restrictions</h1>
	<p>A badge can also be set to require a minimum age and/or a maximum age in which one can buy the pass. Useful for minor badges. Badges can
	also be set to non-purchasable. Non-purchasable badges can only be <strong>given by an Administrator</strong>. Note: This does not prevent
	lying about one's age - that's what ID's are for.</p>		
	<fieldset>	
		<?php 	
		foreach (array('minAge', 'maxAge', 'isPurchasable') as $field)
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
