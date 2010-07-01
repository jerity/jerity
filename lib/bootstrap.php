<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################

# Attempt to pull in core package bootstrap:
if (defined('JERITY_ROOT')) {
  trigger_error('Jerity should only be included once', E_USER_ERROR);
}
define('JERITY_ROOT', rtrim(dirname(__FILE__), '/').'/');
if (!is_readable(JERITY_ROOT.'core/bootstrap.php')) {
  trigger_error('Jerity core package is required at \''.JERITY_ROOT.'core\'', E_USER_ERROR);
}

# Enable strict error reporting:
$_er_ = error_reporting(E_ALL | E_STRICT);

# Pull in Jerity core:
require_once(JERITY_ROOT.'core/bootstrap.php');

# Locate additional Jerity packages for autoloading:
Jerity::addAutoloadDir(JERITY_ROOT.'form');
Jerity::addAutoloadDir(JERITY_ROOT.'rest');
Jerity::addAutoloadDir(JERITY_ROOT.'template');
Jerity::addAutoloadDir(JERITY_ROOT.'ui');

# Restore original error reporting:
error_reporting($_er_);
unset($_er_);
