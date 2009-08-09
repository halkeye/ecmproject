<p>We are excited to welcome you to <?= $convention_name ?></p>

<p>Please follow this link to validate your email: <?= html::anchor($validationUrl) ?> 

<p>Your <?= $convention_name ?> login is (just in case you forgot) is <?= $email ?>  

<p>Questions? Head to the <?= $convention_name ?> forums at <?= html::anchor($convention_forum_url) ?> 
    or email <?= $convention_contact_email ?> 

<p>- <?= $convention_name ?><br />
 <?= html::anchor($convention_url) ?></p>

<br />
--<br />
<p>To stop receiving these notifications, head to the Privacy section at <?= html::anchor('/user/privacy') ?> </p>

