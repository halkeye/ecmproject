<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
<title>Electronic Convention Management (ECM)</title> 
<?php echo html::stylesheet(array('css/main.css')); ?>
</head>
<body>

<div id="container"> 
    <div id="content" class='contentNoMenu'>

<?php

View::set_global('field_lang_prefix', 'ecmproject.formtest_');


echo '<fieldset>';
echo new View('global/_form_field', array(
            'field'=>'dropdown',
            'fieldData'=>array(
                'type'=>'select',
                'values'=>array('1'=>'Choice 1', '2'=>'Choice 2'),
            ),
            'value'=>null,
            'hasError'=>0
));
echo '</fieldset>';

echo '<fieldset>';
echo new View('global/_form_field', array(
            'field'=>'radio',
            'fieldData'=>array(
                'type'=>'radio',
                'values'=>array('1'=>'Choice 1', '2'=>'Choice 2'),
            ),
            'value'=>2,
            'hasError'=>0
));
echo '</fieldset>';

echo '<fieldset>';
echo new View('global/_form_field', array(
            'field'=>'checkbox',
            'fieldData'=>array(
                'type'=>'checkbox',
                'selected'=>1,
            ),
            'value'=>null,
            'hasError'=>0
));
echo '</fieldset>';

echo '<fieldset>';
echo new View('global/_form_field', array(
            'field'=>'date',
            'fieldData'=>array(
                'type'=>'date',
            ),
            'value'=>null,
            'hasError'=>0
));
echo '</fieldset>';

echo '<fieldset>';
echo new View('global/_form_field', array(
            'field'=>'password',
            'fieldData'=>array(
                'type'=>'password',
            ),
            'value'=>null,
            'hasError'=>0
));
echo '</fieldset>';

echo '<fieldset>';
echo new View('global/_form_field', array(
            'field'=>'text',
            'fieldData'=>array(
                'type'=>'text',
            ),
            'value'=>null,
            'hasError'=>0
));
echo '</fieldset>';

echo '<fieldset>';
echo 'Hidden - ';
echo new View('global/_form_field', array(
            'field'=>'hidden',
            'fieldData'=>array(
                'type'=>'hidden',
            ),
            'value'=>1,
            'hasError'=>0
));
echo '</fieldset>';

View::set_global('field_lang_prefix', 'ecmproject.formtest2_');

echo '<fieldset>';
echo new View('global/_form_field', array(
            'field'=>'textarea',
            'fieldData'=>array(
                'type'=>'textarea',
            ),
            'value'=>null,
            'hasError'=>0
));
echo '</fieldset>';
?>
</div></div></body></html>
