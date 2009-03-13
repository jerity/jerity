<html>
<head>
<title>FormGenerator test page</title>
</head>
<body>
<?php
require_once('FormGenerator.php');

/*
$fg = new FormGenerator();
echo '<pre>'.htmlentities($fg->render()).'</pre>';
echo '<hr>';

$fg = new FormGenerator();
$fg->addInput('name', 'Name');
echo '<pre>'.htmlentities($fg->render()).'</pre>';
echo '<hr>';

$fg = new FormGenerator();
$fg->addInput('name', 'Name', array('id'=>'textwoo'));
$fg->addInput('foo', 'Foo');
echo '<pre>'.htmlentities($fg->render()).'</pre>';
echo '<hr>';

$fg = new FormGenerator();
$fg->addInput('name', 'Name', array('id'=>'textwoo'));
$fg->addInput('foo', 'Foo');
echo '<pre>'.htmlentities($fg->render()).'</pre>';
echo '<hr>';
 */
?>
<link rel="stylesheet" type="text/css" href="core.css">
<!--[if lte IE7]><link rel="stylesheet" type="text/css" href="core.ie.le7.css"><![endif]-->
<style type="text/css">
/* ***** BEGIN: body styles ***** */
body {
  font-size: 12px;
  font-family: sans-serif;
}
/* *****  END: body styles  ***** */
</style>
<link rel="stylesheet" type="text/css" href="skin.css">
<?php

$fg = new FormGenerator(false, false);
$fg->setAttribute('class', 'leftlabels');

$fs = $fg->addFieldset('Contact Details');
$fs->addInput('name',  'Name');
$fs->addInput('email', 'Email address');
$cg = $fs->addFieldset('Occupation:');
$cg->addRadio('occupation', 'Butcher', 'butcher');
$cg->addRadio('occupation', 'Baker', 'baker');
$cg->addRadio('occupation', 'Candle-stick maker', 'csm');
$cg->addRadio('occupation', 'Web coder', 'coder');
$cg = $fs->addFieldset('Hobbies:');
$cg->addCheckbox('hobby1', 'Electronics');
$cg->addCheckbox('hobby2', 'Programming');
$cg->addCheckbox('hobby3', 'Martial Arts');
$cg->addCheckbox('hobby4', 'Ballroom Dancing');
$fs->addInput('phone', 'Telephone');

$fs = $fg->addFieldset('Delivery Address');
$fs->addInput('address1', 'Address 1');
$fs->addInput('address2', 'Address 2');
$fs->addInput('suburb',   'Suburb/Town');
$fs->addInput('postcode', 'Postcode');
$fs->addInput('country',  'Country');

$fs = $fg->addFieldset('Comments');
$fs->addTextarea('comments', 'Comments');

$fs = $fg->addFieldset(null, array('class'=>'submit'));
$fs->addSubmit(null,  'Begin download');

$fg->populateFromPost();

echo $fg->render();
#echo '<hr>';
#echo '<pre>'.htmlentities($fg->render()).'</pre>';
?>
</body>
</html>
