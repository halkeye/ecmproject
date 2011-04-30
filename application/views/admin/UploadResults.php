<h3>Successfully imported registrations </h3>
<dl class='success'>
	<?php
		foreach($import_success as $email => $regs) {
			print "<dt>$email</dt>\n";
			foreach ($regs as $reg) {
				printf('<dd>%s - %s</dd>', $reg->reg_id, $reg->gname . ' ' . $reg->sname);
			}
		}
	?>
</dl>	
<br />
<h3>Registrations that failed validation/import.</h3>
<dl class='failure'>
	<?php
		foreach($import_failure as $error) {
			$reg = $error['reg'];
			$errors = $error['errors'];
			
			print '<dt>' . $reg->reg_id . "</dt>\n";
			foreach($errors as $error) {
				print '<dd>' . $error . '</dd>';
			}
		}
	?>
</dl>
