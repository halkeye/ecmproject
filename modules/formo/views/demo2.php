<?php echo$form->open()?>
	<p>
		<label>Name:</label>
		<?php echo$form->name?> 
		<span class="<?php echo$form->name->error_msg_class?>"><?php echo$form->name->error?></span>
	</p>
	<p>
		<label>Email:</label>
		<input type="text" name="email" value="<?php echo$form->email->value?>" class="<?php echo$form->email->class?>" onclick="<?php echo$form->email->onclick?>" />
		<span class="<?php echo$form->email->error_msg_class?>"><?php echo$form->email->error?></span>
	</p>
	<p>
		<label>Image:</label>
		<?php echo$form->image?> 
		<span class="<?php echo$form->image->error_msg_class?>"><?php echo$form->image->error?></span>
	</p>
	<p>
		<?php echo$form->submit?> 
	</p>
<?php echo$form->close()?>