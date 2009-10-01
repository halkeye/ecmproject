<div id='newform'>
<br />
<!-- CONTENT: Expect initial convention id for this pass to be associated to. -->
<?php 
	echo form::open("admin/createPass/$cid"); 
?>
	<h1>General Information</h1>
	<p>At a minimum, a badge requires a name, and a price. Upon creation, this badge will be available for purchase through
	normal registration unless otherwise specified. </p>		
	<fieldset>		
		<label for="name">Pass Name <span class="small">Max: 100 characters.</span></label>
		<?php print form::input('name', $row['name']); ?>
		<label for="price">Price <span class="small">Dollars and Cents.</span></label>
		<?php print form::input('price', $row['price']); ?>
	</fieldset>
	<h1>Availability</h1>
	<p>A badge can be set to be available for a period of time. For instance, a badge can be set so that it is only purchasable from October 23rd, 2009 to
	December 23rd, 2009. </p>		
	<fieldset>		
		<!-- Change to three fields -->
		<label for="startDate">Start Date <span class="small">Format: MM/DD/YYYY</span></label>
		<?php print form::input('startDate', $row['startDate']); ?>
		<label for="endDate">End Date <span class="small">Format:  MM/DD/YYYY</span></label>
		<?php print form::input('endDate', $row['endDate']); ?>
	</fieldset>
	<h1>Restrictions</h1>
	<p>A badge can also be set to require a minimum age and/or a maximum age in which one can buy the pass. Useful for minor badges. Badges can
	also be set to non-purchasable. Non-purchasable badges can only be <strong>given.</strong></p>		
	<fieldset>		
		<label for="minAge">Minimum Age <span class="small">Enter in age in years.</span></label>
		<?php print form::input('minAge', $row['minAge']); ?>
		<label for="maxAge">Maximum Age <span class="small">Enter in age in years.</span></label>
		<?php print form::input('maxAge', $row['maxAge']); ?>
		<label for="isPurchasable">Purchasable? <span class="small">Check if purchasable.</span></label>
		<?php print form::checkbox('isPurchasable', 'isPurchasable', $row['isPurchasable']); //Fix checkbox alignment! ?>
	</fieldset>
	<fieldset>				
		<button type="submit">Continue</button>
	</fieldset>							
<?php echo form::close(); ?>
</div>