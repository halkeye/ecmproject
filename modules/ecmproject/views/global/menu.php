<?php foreach ($menu as $m): ?>
<li>
<?php
if (isset($m['seperator'])) { echo '-----'; }
else { echo html::anchor($m['url'], $m['title']); }
?>
</li>
<?php endforeach; ?>
