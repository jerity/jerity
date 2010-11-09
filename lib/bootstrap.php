<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity
 */

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

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
