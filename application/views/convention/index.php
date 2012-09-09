<!-- THIS NEEDS A BETTER NESS...this isn't even used any more...(replaced with shopping cart/my account view. -->
<h2>Registrations:</h2>
<?php
foreach ($conventions as $convention)
{
    echo '<h2>' . html::chars($convention->name) . '</h2>'."\n";
    if (count($convention->regs))
    {
        echo '<ul>';
        foreach ($convention->regs as $reg)
        {
            echo '<li>';
            echo html::chars($reg->badge);
            echo '</li>';
        }
        echo "</ul>\n";
    }
}
?>


