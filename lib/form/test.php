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
form fieldset {
  margin: 1.5em 0 0 0;
  padding: 0;
}
form legend {
  margin-left: 1em;
  color: #000;
  font-weight: bold;
}
form fieldset ul {
  padding: 1em 1em 0 1em;
  list-style: none;
}
form fieldset li {
  padding-bottom: 1em;
}
form fieldset.submit {
  border-style: none;
}
/* *****  END: core styles  ***** */
/* ***** BEGIN: top labels ***** */
form.toplabels label {
  display: block;
}
/* *****  END: top labels  ***** */
/* ***** BEGIN: left labels ***** */
form.leftlabels label {
  float: left;
  width: 10em;
  margin-right: 1em;
}
form.leftlabels fieldset li {
  float: left;
  clear: left;
  width: 100%;
}
form.leftlabels fieldset {
  float: left;
  clear: left;
  width: 100%;
}
form.leftlabels fieldset.submit {
  float: none;
  width: auto;
  padding-left: 11em;
}
/* *****  END: left labels  ***** */
/* NOTE: right labels do NOT work with nested fieldsets */
/* ***** BEGIN: right labels ***** */
form.ralignlabels label {
  text-align: right;
}
/* *****  END: right labels  ***** */
/* ***** BEGIN: fieldset styling part 1 ***** */
form legend {
  padding: 0;
}
form fieldset {
  border: 1px solid #bfbab0;
  background-color: #f2efe9;
}
form fieldset.submit {
  border-style: none;
  background-color: transparent;
}
/* *****  END: fieldset styling part 1  ***** */
/* ***** BEGIN: fieldset styling part 2 ***** */
form fieldset {
  margin: 0 0 -1em 0;
  padding: 0 0 1em 0;
  border-style: none;
  border-top: 1px solid #bfbab0;
}
form legend span {
  position: absolute;
  margin-top: 0.5em;
  font-size: 135%;
}
/* *****  END: fieldset styling part 2  ***** */
/* ***** BEGIN: firefox bugfix ***** */
form fieldset {
  position: relative;
}
form legend span {
  left: 0.74em;
  top: 0;
}
form legend {
  margin-left: 0;
}
form fieldset ul {
  padding: 3em 1em 0 1em;
}
form fieldset.submit {
  background-color: #fff;
}
form fieldset.submit ul {
  padding-top: 0.5em;
}
/* *****  END: firefox bugfix  ***** */
/* ***** BEGIN: nested fieldsets ***** */
form fieldset fieldset, form.leftlabels fieldset fieldset {
  margin-bottom: -1.5em;
  border-style: none;
  background-color: transparent;
  background-image: none;
}
form fieldset fieldset legend {
  margin-left: 0;
  font-weight: normal;
}
form fieldset fieldset legend span {
  font-size: inherit;
  left: 0;
}
form.leftlabels fieldset fieldset ul {
  position: relative;
  top: 0;
  margin: 0 0 0 11em;
  padding: 0;
}
form.toplabels fieldset fieldset ul {
  padding: 2.5em 1em 0 0;
}
form fieldset fieldset label, form.toplabels fieldset fieldset label, form.leftlabels fieldset fieldset label {
  float: none;
  width: auto;
  margin-right: auto;
  display: inline;
}
/* *****  END: nested fieldsets  ***** */
</style>
<!--[if lte IE7]>
<style type="text/css">
/* ***** BEGIN: IE fix for fieldsets ***** */
form legend {
  position: relative;
  left: -7px;
  top: -0.75em;
}
form fieldset ul {
  padding-top: 0.25em;
  zoom: 1;
}
form fieldset {
  position: relative;
}
/* *****  END: IE fix for fieldsets  ***** */
/* ***** BEGIN: firefox bugfix (ie)  ***** */
form legend span {
  margin-top: 1.25em;
}
form fieldset ul {
  padding-top: 3.25em;
  zoom: 1;
}
/* *****  END: firefox bugfix (ie)   ***** */
</style>
<![endif]-->
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
