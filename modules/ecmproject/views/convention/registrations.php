
<h2>Registrations:</h2>
<?php
foreach ($conventions as $convention)
{
    echo '<h2>' . html::specialchars($convention->name) . '</h2>'."\n";
    if (count($convention->regs))
    {
        foreach ($convention->regs as $reg)
        {
            echo '<div>';
            $name = html::specialchars($reg->badge);
            if ($reg->incomplete)
            {
                echo html::anchor(Convention_Controller::STEP1."/" . $reg->id,$name);
                echo ' <span style="color: red">(Incomplete)</span>';
            }
            else
            {
                echo $name;
            }
            echo '</div>';
        }
        echo "\n";
    }
}
?>
    

