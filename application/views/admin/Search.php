<div id="list">	
	<?php echo form::open("$callback", array('class' => 'f_right')); ?>	
	<label for='search_term'>Search: </label>
	<input type='text' id='search_term' name='search_term' class='inline'></input> 	
	<button type='submit'>Search</button>	
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
