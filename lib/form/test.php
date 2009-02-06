<?php
require_once('FormGenerator.php');

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

