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

# Enable strict error reporting:
$_er_ = error_reporting(E_ALL | E_STRICT);

# Pull in Jerity core:
require_once(JERITY_ROOT.'Jerity.class.php');
RenderContext::set(RenderContext::create(RenderContext::TYPE_HTML4_STRICT));

# Locate Jerity classes for autoloading:
Jerity::addAutoloadDir(JERITY_ROOT);

# Restore original error reporting:
error_reporting($_er_);
unset($_er_);
