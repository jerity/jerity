<?php
##############################################################################
# Copyright © 2010 David Ingram, Nicholas Pope
#
# This work is licenced under the Creative Commons BSD License License. To
# view a copy of this licence, visit http://creativecommons.org/licenses/BSD/
# or send a letter to Creative Commons, 171 Second Street, Suite 300,
# San Francisco, California 94105, USA.
##############################################################################

# Set up strict error reporting
error_reporting(E_ALL | E_STRICT);

# check for Jerity core being available
$jerity_top = null;
foreach (array('core/../', '', 'jerity-') as $dirprefix) {
  $dir = dirname(dirname(__FILE__)).'/'.$dirprefix.'core';
  if (is_dir($dir) && is_readable($dir.'/bootstrap.php') && is_readable($dir.'/Jerity.class.php')) {
    $jerity_top = realpath(dirname(dirname(__FILE__)).'/'.$dirprefix);
    if (is_dir($jerity_top)) $jerity_top .= '/';
    break;
  }
}

if (is_null($jerity_top)) {
  die('Jerity core is required for the test suite, and must be in the directory "'.dirname(dirname(__FILE__)).'/core"'."\n");
}

# Pull in Jerity core
require_once("{$jerity_top}core/bootstrap.php");

# Locate additional Jerity packages for autoloading:
Jerity::addAutoloadDir("{$jerity_top}form");
Jerity::addAutoloadDir("{$jerity_top}template");
Jerity::addAutoloadDir("{$jerity_top}ui");

define('DATA_DIR', dirname(__FILE__).'/data/');
