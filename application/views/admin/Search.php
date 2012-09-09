<div id="list">
	<?php echo form::open("$callback", array('class' => 'float_right')); ?>
	<div>
		<label for='search_term' class='nomar'>Search: </label>
		<input type='text' id='search_term' name='search_term' class='inline'></input>
		<input type='submit' value='Search' class='submit'>
	</div>
	<?php echo form::close(); ?>

	<table width='100%'>
	<?php
		if ($rows != null)
		{
			foreach ($rows as $row):
				print $row;
			endforeach;
		}
	?>
	</table>
</div>
