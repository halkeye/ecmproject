<div id="list">
	<?php 
		if (isset($crows) && isset($convention_id)) {
			echo form::open("$callback");		
	?>
	
		<p class='right'>
			<label for="convention_id">Viewing Convention: </label>		
			<?php echo form::dropdown('convention_id', $crows, $convention_id); ?>	
			<button type='submit'>Go</button>			
		</p>
		
	<?php
			echo form::close();
		} 
	?>
	
	<?php echo form::open("admin/search/$entity"); ?>
	<p class='floatRight'>
		<label for='search_term'>Search: </label> <input type='text' id='search_term' name='search_term'></input> <button type='submit'>Search</button>	
	</p>
	<?php echo form::close(); ?>

	<p>
		<?php echo html::anchor($createLink, $createText); ?>
	</p>
	
	<table width='100%'>
	<?php 
		foreach ($rows as $row):
			print $row;	
		endforeach;	
	?>
	</table>	
	<p class='right'>
		<?php 
		
		if (isset($convention_id))
		{
			$pagination = new Pagination(array(
				'base_url'    => "$callback", // base_url will default to current uri
				'uri_segment'    => "$convention_id", // pass a string as uri_segment to trigger former 'label' functionality
				'total_items'    => $total_rows, // use db count query here of course
				'items_per_page' => Controller_Admin::ROWS_PER_PAGE, // it may be handy to set defaults for stuff like this in config/pagination.php
				'style'          => 'digg', // pick one from: classic (default), digg, extended, punbb, or add your own!				
			));			
		}
		else
		{
			$pagination = new Pagination(array(
			'base_url'    => "$callback", // base_url will default to current uri
			//'uri_segment'    => "$convention_id", // pass a string as uri_segment to trigger former 'label' functionality
			'total_items'    => $total_rows, // use db count query here of course
			'items_per_page' => Controller_Admin::ROWS_PER_PAGE, // it may be handy to set defaults for stuff like this in config/pagination.php
			'style'          => 'digg', // pick one from: classic (default), digg, extended, punbb, or add your own!
		));	
		}
			
		echo $pagination->render('digg');		
		?>
	</p>
</div>
