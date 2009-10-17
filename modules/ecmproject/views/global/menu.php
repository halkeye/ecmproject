<?php
foreach ($menu as $m)
{
    if (isset($m['seperator'])) 
    { 
        echo '<li>-----</li>'; 
        continue;
    }

    $attributes = array(
            'class'=>'menuitem',
    );
    if ($m['url'] == url::current()) { $attributes['class'] .= ' currentMenuChoice'; }
    echo '<li ' . html::attributes($attributes) . '>';
    echo html::anchor($m['url'], $m['title']); 
    echo '</li>';
}
