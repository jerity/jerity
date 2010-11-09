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

# Enable strict error reporting:
$_er_ = error_reporting(E_ALL | E_STRICT);

# Include the core Jerity utility class
require_once(JERITY_ROOT.'Jerity.class.php');

# Add Jerity Core as an autoload directory
Jerity::addAutoloadDir(JERITY_ROOT);

# Set default global render context to HTML 4.01 Strict
RenderContext::set(RenderContext::create(RenderContext::TYPE_HTML4_STRICT));

# Restore original error reporting:
error_reporting($_er_);
unset($_er_);

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
