<p>Due to legal and privacy reasons we are unable to post attendees nameson this list instead please search for your badge name. If you didn&#8217;t include a badge name on your registration and have not received your letter after a 6 week period please contact registration at <a href="mailto:registration@animeevolution.com">registration@animeevolution.com</a>.</p>
<br />

<div style="font-size:14px;" align="center">
<a name="top"></a>
<?php
$letters = range('A', 'Z');
// Use of character sequences introduced in 4.1.0
// array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i');
echo "<a href=\"##\">#</a>";
foreach ($letters as $letter) {
    echo " | <a href=\"#$letter\">$letter</a>\n";
}
?>
</div>

<?php foreach (array_merge(array('#'), $letters) as $letter): ?>
<?php if (!@$preRegs[$letter]) { continue; } ?>
<br />
<p><a name="<?php echo $letter;?>"><strong><span style="font-size:14px; color:#000000; text-decoration:underline;"><?php echo $letter ?></span></strong></a></p>
<table cellpadding="0" cellspacing="0" border="0">
<?php foreach ($preRegs[$letter] as $preReg) echo "<tr><td>" . htmlentities($preReg, ENT_COMPAT, 'UTF-8'). "</td></tr>"; ?>
</table>
<br />
<p>[<a href="#top">Back to Top</a>]</p>
<br />

<?php endforeach ?>

