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
/* ***** BEGIN: top labels ***** */
label {
  display: block;
}
/* *****  END: top labels  ***** */
</style>
<?php

$fg = new FormGenerator(false, false);

$fs = $fg->addFieldset('Contact Details');
$fs->addInput('name',  'Name');
$fs->addInput('email', 'Email address');
$fs->addInput('phone', 'Telephone');

$fs = $fg->addFieldset('Delivery Address');
$fs->addInput('address1', 'Address 1');
$fs->addInput('address2', 'Address 2');
$fs->addInput('suburb',   'Suburb/Town');
$fs->addInput('postcode', 'Postcode');
$fs->addInput('country',  'Country');

$fs = $fg->addFieldset(null, array('class'=>'submit'));
$fs->addSubmit(null,  'Begin download');

echo $fg->render();
echo '<hr>';
echo '<pre>'.htmlentities($fg->render()).'</pre>';

