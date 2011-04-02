<div id="list">	
	<?php echo form::open("$callback"); ?>
	<p class='floatRight'>
		<label for='search_term'>Search: </label> <input type='text' id='search_term' name='search_term'></input> <button type='submit'>Search</button>	
	</p>
	<p>
	
	</p>
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
