<?php
error_reporting(E_ALL | E_NOTICE | E_STRICT);
?>
<html>
<head>
<title>FormGenerator test page</title>
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
</head>
<body>
<?php
require_once('FormGenerator.php');

$fg = new FormGenerator(false, false);
$fg->setAttribute('class', 'leftlabels');

$fs = $fg->addFieldset('Contact Details');
$fs->addInput('name',  'Name', array('required'=>true));
$fs->addInput('email', 'Email address', array('required'=>true));
$fs->addSelect('units', 'Units', array('metric'=>'Metric', 'imperial'=>'Imperial'));
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
$fs->addHint('Please include your international country code.');

$fs = $fg->addFieldset('Delivery Address');
$fs->addInput('address1', 'Address 1', array('required'=>true));
$fs->addInput('address2', 'Address 2');
$fs->addInput('suburb',   'Suburb/Town', array('required'=>true));
$fs->addInput('postcode', 'Postcode', array('required'=>true));
$fs->addInput('country',  'Country', array('required'=>true));

$fs = $fg->addFieldset('Comments');
$fs->addTextarea('comments', 'Comments');

$fs = $fg->addFieldset(null, array('class'=>'submit'));
$fs->addSubmit(null,  'Begin download');

$fg->populateFromPost();

echo $fg->render();
?>
</body>
</html>
