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

/**
 *
 */
define('JERITY_ROOT_PATH'  , rtrim(__DIR__, '/'));

/**
 *
 */
define('JERITY_PHP_VERSION', '5.3');

# Check PHP version:
if (version_compare(PHP_VERSION, JERITY_PHP_VERSION, '<')) {
  trigger_error('Jerity requires PHP '.JERITY_PHP_VERSION.' or later.', E_USER_ERROR);
}

# Enable full error reporting (including strict standards and deprecated):
$_er_ = error_reporting(E_ALL | E_STRICT | E_DEPRECATED);

# Include the core utility class:
require_once JERITY_ROOT_PATH.'/Base.php';

# Add root folder as an autoload directory:
\Jerity\Base::addAutoloadDir(JERITY_ROOT_PATH);

# Set default global render context to HTML 4.01 Strict:
\Jerity\Core\RenderContext::set(
  \Jerity\Core\RenderContext::create(
    \Jerity\Core\RenderContext::TYPE_HTML4_STRICT));

# Restore original error reporting:
error_reporting($_er_);
unset($_er_);

# vim:et:ts=2:sts=2:sw=2:nowrap:ft=php:fdm=marker
