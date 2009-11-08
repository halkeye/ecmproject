<div id='list'>
<?php
	echo '<p><strong>Payment Status:</strong> ' . Registration_Model::regStatusToString($reg['status']) . '</p>';
	echo '<p><strong>Badge Type:</strong> ' . $pass['name'] . '<br /><strong>Price: </strong>$' . $pass['price'] . '</p>';
	echo '<p><strong>Email:</strong> ' . $reg['email'] . '<br /><strong>DOB: </strong>' . $reg['dob'] . '</p>';
?>

	<p>
		<?php echo html::anchor($createLink, $createText, $createText); ?>
	</p>
	
	<table width='100%'>
	<?php 
		foreach ($rows as $row):
			print $row;	
		endforeach;	
	?>
	</table>
</div>