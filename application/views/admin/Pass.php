<div id='form'>
<br />
<!-- CONTENT: Expect initial convention id for this pass to be associated to. -->
<?php 	
	View::set_global('field_lang_prefix', 'field_');
	
	/* Set default values for unset variables. */
	if (!isset($row['name']))
		$row['name'] = '';
		
	if (!isset($row['price']))
		$row['price'] = '';
		
	if (!isset($crows))
		$crows = array();
	
	if (!isset($convention_id))
		$convention_id = '';
		
	if (!isset($row['isPurchasable']))
		$row['isPurchasable'] = 0;

	echo form::open("admin/$callback"); 
?>
	<h3>General Information</h3>
	<p>At a minimum, a ticket needs a name and a price. This ticket will then be available for purchase unless otherwise specified (see Availability and Restrictions) </p>		
	<fieldset>		
		<?php
			foreach (array('name', 'price', 'convention_id') as $field)
			{
				echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
			}		
		?>
	</fieldset>
	<h3>Availability</h3>
	<p>A ticket can be set to be available for sale in a given period of time. For instance, a ticket can be set so that it is only purchasable from October 23rd, 2009 to
	December 23rd, 2009. </p>		
	<fieldset>	
		<?php 	
		foreach (array('startDate', 'endDate') as $field)
		{
			echo new View('global/_form_field', array('field'=>$field, 'fieldData'=>$fields[$field], 'value' => $row[$field], 'hasError'=>isset($errors[$field]) && $errors[$field]));
		}
		?>
	</fieldset>
	<h3>Restrictions</h3>
	<p>Tickets can be restricted further by limiting the amount available (and/or) disabling it from being purchased. This is useful if certain ticket types must be given 
	(instead of purchased through the system). <strong>Leave Tickets Available blank</strong> if no limit is being imposed.</p>		
	<fieldset>	
		<div>
		<?php 
			echo Form::label('tickets_total', 'Tickets Available', array('class' => 'nosub'));
			echo Form::input ('tickets_total', $row['tickets_total']);	
		?>
		</div>
		
		<?php		 	
		foreach (array('isPurchasable') as $field)
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
