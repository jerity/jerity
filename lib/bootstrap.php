<?php
if (defined('JERITY_ROOT')) {
  throw new Exception('Jerity should only be included once');
}

define('JERITY_ROOT', rtrim(dirname(__FILE__), '/').'/');

if (!is_readable(JERITY_ROOT.'core/bootstrap.php')) {
  if (is_dir(JERITY_ROOT.'.git')) {
    trigger_error('Jerity core is required. Please ensure you have run "git submodule init" and "git submodule update" in the repository.', E_USER_ERROR);
  } else {
    trigger_error('Jerity core is required. Please ensure it is in the directory '.JERITY_ROOT.'core', E_USER_ERROR);
  }
}

require_once(JERITY_ROOT.'core/bootstrap.php');

# Set up strict error reporting
$_jerity_er = error_reporting(E_ALL | E_STRICT | E_NOTICE);

# Pull in Jerity core
require_once(JERITY_ROOT.'core/bootstrap.php');

# Locate additional Jerity packages for autoloading:
Jerity::addAutoloadDir(JERITY_ROOT.'form');
Jerity::addAutoloadDir(JERITY_ROOT.'template');
Jerity::addAutoloadDir(JERITY_ROOT.'ui');

error_reporting($_jerity_er);
unset($_jerity_er);
