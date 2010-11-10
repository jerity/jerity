<?php
/**
 * @author     Dave Ingram <dave@dmi.me.uk>
 * @author     Nick Pope <nick@nickpope.me.uk>
 * @copyright  Copyright (c) 2010, Dave Ingram, Nick Pope
 * @license    http://creativecommons.org/licenses/BSD/ CC-BSD
 * @package    jerity
 */

# Don't include Jerity more than once:
if (defined('JERITY_ROOT_PATH')) {
  trigger_error('Jerity should not be included more than once.', E_USER_WARNING);
  return;
}

# Set some constants:
define('JERITY_ROOT_PATH'  , rtrim(dirname(__FILE__), '/'));

# Enable full error reporting (including strict standards and deprecated):
$_er_ = error_reporting(E_ALL | E_STRICT);

# Include the core utility class:
require_once(JERITY_ROOT_PATH.'/Jerity.class.php');

# Add root folder as an autoload directory:
Jerity::addAutoloadDir(JERITY_ROOT_PATH);

# Set default global render context to HTML 4.01 Strict:
RenderContext::set(RenderContext::create(RenderContext::TYPE_HTML4_STRICT));

# Restore original error reporting:
error_reporting($_er_);
unset($_er_);

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
