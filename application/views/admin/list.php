<div id="list">
	<p><strong><?php echo html::anchor($createLink, $createText, null, null, true); ?></strong></p>
	
	<?php 
		if (isset($crows) && isset($convention_id)) {
			echo form::open("$callback", array('class' => 'float_left'));		
	?>		
		<div>
			<label for="convention_id" class='nomar'>...for Event: </label>
			<?php echo form::select('convention_id', $crows, $convention_id); ?>
			<input type='submit' value='Switch Events' class='submit'>	
		</div>
		
	<?php echo form::close(); } ?>
	
	
	<?php echo form::open("admin/search/$entity", array('class' => 'float_right')); ?>	
	<div>
		<label for='search_term' class='nomar'>Search: </label>
		<input type='text' id='search_term' name='search_term' class='inline'></input> 	
		<input type='submit' value='Search' class='submit'>
	</div>
	<?php echo form::close(); ?>

	<table width='100%'>
	<?php 
		foreach ($rows as $row):
			print $row;	
		endforeach;	
	?>
	</table>	
	<p>
		<?php 
		
		if (isset($convention_id))
		{
			$pagination = new Pagination(array(
				'base_url'    => "$callback", // base_url will default to current uri
				'uri_segment'    => "$convention_id", // pass a string as uri_segment to trigger former 'label' functionality
				'total_items'    => $total_rows, // use db count query here of course
				'items_per_page' => Controller_Admin::ROWS_PER_PAGE, // it may be handy to set defaults for stuff like this in config/pagination.php
			));			
		}
		else
		{
			$pagination = new Pagination(array(
			'base_url'    => "$callback", // base_url will default to current uri
			//'uri_segment'    => "$convention_id", // pass a string as uri_segment to trigger former 'label' functionality
			'total_items'    => $total_rows, // use db count query here of course
			'items_per_page' => Controller_Admin::ROWS_PER_PAGE, // it may be handy to set defaults for stuff like this in config/pagination.php
		));	
		}
			
		echo $pagination->render();		
		?>
	</p>
</div>
