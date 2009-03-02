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
<style type="text/css">
/* ***** BEGIN: body styles ***** */
body {
  font-size: 12px;
  font-family: sans-serif;
}
/* *****  END: body styles  ***** */
/* ***** BEGIN: core styles ***** */
fieldset {
  margin: 1.5em 0 0 0;
  padding: 0;
}
legend {
  margin-left: 1em;
  color: #000;
  font-weight: bold;
}
fieldset ul {
  padding: 1em 1em 0 1em;
  list-style: none;
}
fieldset li {
  padding-bottom: 1em;
}
fieldset.submit {
  border-style: none;
}
/* *****  END: core styles  ***** */
/* ***** BEGIN: top labels ***** *
label {
  display: block;
}
 * *****  END: top labels  ***** */
/* ***** BEGIN: left labels ***** */
label {
  float: left;
  width: 10em;
  margin-right: 1em;
}
fieldset li {
  float: left;
  clear: left;
  width: 100%;
}
fieldset {
  float: left;
  clear: left;
  width: 100%;
}
fieldset.submit {
  float: none;
  width: auto;
  padding-left: 11em;
}
/* *****  END: left labels  ***** */
/* NOTE: right labels do NOT work with nested fieldsets */
/* ***** BEGIN: right labels ***** *
label {
  text-align: right;
}
 * *****  END: right labels  ***** */
/* ***** BEGIN: fieldset styling part 1 ***** */
legend {
  padding: 0;
}
fieldset {
  border: 1px solid #bfbab0;
  background-color: #f2efe9;
}
fieldset.submit {
  border-style: none;
  background-color: transparent;
}
/* *****  END: fieldset styling part 1  ***** */
/* ***** BEGIN: fieldset styling part 2 ***** */
fieldset {
  margin: 0 0 -1em 0;
  padding: 0 0 1em 0;
  border-style: none;
  border-top: 1px solid #bfbab0;
}
legend span {
  position: absolute;
  margin-top: 0.5em;
  font-size: 135%;
}
/* *****  END: fieldset styling part 2  ***** */
/* ***** BEGIN: firefox bugfix ***** */
fieldset {
  position: relative;
}
legend span {
  left: 0.74em;
  top: 0;
}
legend {
  margin-left: 0;
}
fieldset ul {
  padding: 3em 1em 0 1em;
}
fieldset.submit {
  background-color: #fff;
}
fieldset.submit ul {
  padding-top: 0.5em;
}
/* *****  END: firefox bugfix  ***** */
/* ***** BEGIN: nested fieldsets ***** */
fieldset fieldset {
  margin-bottom: -1.5em;
  border-style: none;
  background-color: transparent;
  background-image: none;
}
fieldset fieldset legend {
  margin-left: 0;
  font-weight: normal;
}
fieldset fieldset legend span {
  font-size: inherit;
  left: 0;
}
fieldset fieldset ul {
  position: relative;
  top: 0;
  margin: 0 0 0 11em;
  padding: 0;
}
fieldset fieldset label {
  float: none;
  width: auto;
  margin-right: auto;
}
/* *****  END: nested fieldsets  ***** */
</style>
<!--[if lte IE7]>
<style type="text/css">
/* ***** BEGIN: IE fix for fieldsets ***** */
legend {
  position: relative;
  left: -7px;
  top: -0.75em;
}
fieldset ul {
  padding-top: 0.25em;
  zoom: 1;
}
fieldset {
  position: relative;
}
/* *****  END: IE fix for fieldsets  ***** */
/* ***** BEGIN: firefox bugfix (ie)  ***** */
legend span {
  margin-top: 1.25em;
}
fieldset ul {
  padding-top: 3.25em;
  zoom: 1;
}
/* *****  END: firefox bugfix (ie)   ***** */
</style>
<![endif]-->
<?php

$fg = new FormGenerator(false, false);

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

echo $fg->render();
#echo '<hr>';
#echo '<pre>'.htmlentities($fg->render()).'</pre>';
?>
</body>
</html>
