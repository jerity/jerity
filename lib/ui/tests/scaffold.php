<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################

require_once(dirname(dirname(dirname(__FILE__))).'/core/bootstrap.php');
Jerity::addAutoloadDir(dirname(dirname(dirname(__FILE__))).'/form');
Jerity::addAutoloadDir(dirname(dirname(dirname(__FILE__))).'/template');
Jerity::addAutoloadDir(dirname(dirname(dirname(__FILE__))).'/ui');

$a = new FormGenerator();

$db = new PDO('mysql:host=localhost;dbname=dmi_personal', 'dmi_personal', 'h6LZrbNZB7TSVRmb');

$schema = array(
  'foo' => array(
    'fields' => array(
      'id' => array('uint', 'primary'),
      'name' => array('varchar(40)', 'display'),
      'mod' => 'boolean',
      'points' => 'int',
    ),
    'hasMany' => array(
      'bar' => 'foo_id',
    ),
  ),
  'bar' => array(
    'fields' => array(
      'id' => array('uint', 'primary'),
      'qux' => 'int',
      'baz' => 'varchar(25)',
      'title' => array('varchar(40)', 'display'),
      'foo_id' => 'uint',
    ),
    'belongsTo' => array(
      'foo' => 'foo_id',
    ),
  ),
);

#$scaffold = new Scaffold($schema, $db);
#$scaffold->processActions();
#$f = new FormGenerator(true);
##$scaffold->generateCreateForm('bar', $f);
#$scaffold->generateUpdateForm('foo', 1, $f);
#$f->addSubmit('', 'Update');
#echo $f->render();
echo Scaffold::doScaffold($schema, $db, 'foo');
