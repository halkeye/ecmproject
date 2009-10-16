<?php foreach ($menu as $m): ?>
<li>
<?php
if (isset($m['seperator'])) 
{ 
    echo '-----'; 
}
else
{
    $attributes = array();
    if ($m['url'] == url::current()) { $attributes['class'] = 'currentMenuChoice'; }
    echo html::anchor($m['url'], $m['title'], $attributes); 
}
?>
</li>
<?php endforeach; ?>
