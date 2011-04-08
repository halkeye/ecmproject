<?php
foreach ($menu as $m)
{
    if (isset($m['seperator'])) 
    { 
        echo '<li>-----</li>'; 
        continue;
    }

    $attributes = array(
            'class'=>'menuItem',
    );
    if ($m['url'] == $currentUrl) 
    {
        $attributes['class'] .= ' currentMenuChoice'; 
    }
    echo '<li ' . html::attributes($attributes) . '>';
    echo html::anchor(url::site($m['url'], TRUE), $m['title']); 
    echo '</li>';
}
